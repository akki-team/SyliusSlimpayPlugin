<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandHandler;

use Akki\SyliusSlimpayPlugin\Api\ClientFactoryInterface;
use Akki\SyliusSlimpayPlugin\Command\CapturePaymentRequest;
use Akki\SyliusSlimpayPlugin\Constants\Constants;
use Akki\SyliusSlimpayPlugin\Provider\MandateFieldsProviderInterface;
use Akki\SyliusSlimpayPlugin\Util\ResourceSerializer;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\CoreBundle\OrderPay\Provider\UrlProviderInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Ouvre la souscription Slimpay et rend l'URL vers laquelle envoyer le client.
 *
 * Remplace l'enchainement Payum `CaptureAction' -> `SignMandateAction' -> `CheckoutRedirectAction',
 * qui communiquait par un modele tableau mute de proche en proche et signalait la redirection en
 * levant une exception `HttpRedirect'. Ici l'URL passe par `setResponseData()', et c'est le
 * fournisseur de reponse HTTP qui la transforme en redirection -- la voie prescrite par Sylius.
 */
#[AsMessageHandler(bus: 'sylius.payment_request.command_bus')]
final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private ClientFactoryInterface $clientFactory,
        private MandateFieldsProviderInterface $mandateFieldsProvider,
        private UrlProviderInterface $afterPayUrlProvider,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);

        if (PaymentRequestInterface::STATE_PROCESSING === $paymentRequest->getState()) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $paymentRequest->getPayment();
        $api = $this->clientFactory->createFromPaymentMethod($paymentRequest->getMethod());

        $details = $payment->getDetails();
        $scheme = $details['payment_scheme'] ?? Constants::PAYMENT_SCHEME_SEPA_DIRECT_DEBIT_CORE;
        $checkoutMode = $details['checkout_mode'] ?? $api->getDefaultCheckoutMode();
        $subscriberReference = $this->mandateFieldsProvider->provideSubscriberReference($payment);

        if (Constants::PAYMENT_SCHEME_CARD === $scheme) {
            $order = $api->setUpCardAlias($subscriberReference);
        } else {
            $order = $api->signMandate(
                $subscriberReference,
                $scheme,
                $this->mandateFieldsProvider->provide($payment),
                $this->afterPayUrlProvider->getUrl($paymentRequest, UrlGeneratorInterface::ABSOLUTE_URL),
                0,
                (string) $payment->getCurrencyCode(),
            );
        }

        $details['payment_scheme'] = $scheme;
        $details['checkout_mode'] = $checkoutMode;
        $details['order'] = ResourceSerializer::serializeResource($order);
        $payment->setDetails($details);

        // Seule la redirection est reellement empruntee : les trois passerelles ont
        // `default_checkout_mode' a `redirect'. Le cadre integre reste a porter si un canal
        // repasse dessus un jour.
        if (Constants::CHECKOUT_MODE_REDIRECT !== $checkoutMode) {
            throw new \LogicException(sprintf(
                'Slimpay checkout mode "%s" is not supported yet, only "%s" is.',
                (string) $checkoutMode,
                Constants::CHECKOUT_MODE_REDIRECT,
            ));
        }

        $paymentRequest->setResponseData([
            'url' => $api->getCheckoutRedirect($order),
        ]);

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_PROCESS,
        );
    }
}
