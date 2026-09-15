<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandHandler;

use Akki\SyliusSlimpayPlugin\Command\CaptureEndPaymentRequest;
use Akki\SyliusSlimpayPlugin\Processor\PaymentTransitionProcessorInterface;
use Akki\SyliusSlimpayPlugin\Util\ResourceSerializer;
use HapiClient\Hal\Resource;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Reprend une capture deja engagee, au lieu d'ouvrir un second ordre Slimpay.
 *
 * Sans equivalent en 1.14, ou chaque clic sur payer creait un nouvel ordre : Payum n'avait pas
 * d'etat de demande a respecter. Sylius 2 en a un, et il faut donc decider du sort de la demande :
 *
 *   - ordre encore ouvert : on la laisse en `processing'. Le fournisseur de reponse HTTP rendra
 *     l'URL deja stockee, et le client reprend sa signature de mandat la ou il l'avait laissee.
 *   - ordre clos : on la rend finale. `PaymentRequestPayResponseProvider' en creera une neuve au
 *     clic suivant, et `PaymentRequestPayAction' bascule entre-temps sur l'apres-paiement faute de
 *     fournisseur de reponse. Sans ca, un abandon cote Slimpay laisse le client sur une URL morte.
 */
#[AsMessageHandler(bus: 'sylius.payment_request.command_bus')]
final readonly class CaptureEndPaymentRequestHandler
{
    private const PREFIXE_ETAT_OUVERT = 'open';

    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private PaymentTransitionProcessorInterface $paymentTransitionProcessor,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(CaptureEndPaymentRequest $captureEndPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($captureEndPaymentRequest);

        if (PaymentRequestInterface::STATE_PROCESSING !== $paymentRequest->getState()) {
            return;
        }

        // Relit l'ordre chez Slimpay, met a jour les `details' et fait avancer le paiement.
        $this->paymentTransitionProcessor->process($paymentRequest);

        if ($this->orderIsStillOpen($paymentRequest)) {
            return;
        }

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );
    }

    private function orderIsStillOpen(PaymentRequestInterface $paymentRequest): bool
    {
        /** @var PaymentInterface $payment */
        $payment = $paymentRequest->getPayment();
        $serializedOrder = $payment->getDetails()['order'] ?? null;

        if (null === $serializedOrder) {
            return false;
        }

        $order = ResourceSerializer::unserializeResource($serializedOrder);

        if (false === $order instanceof Resource) {
            return false;
        }

        return str_starts_with((string) ($order->getState()['state'] ?? ''), self::PREFIXE_ETAT_OUVERT);
    }
}
