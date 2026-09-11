<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Processor;

use Akki\SyliusSlimpayPlugin\Api\Api;
use Akki\SyliusSlimpayPlugin\Api\ClientFactoryInterface;
use Akki\SyliusSlimpayPlugin\Constants\Constants;
use Akki\SyliusSlimpayPlugin\Util\ResourceSerializer;
use HapiClient\Hal\Resource;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentTransitions;

/**
 * Relit la souscription chez Slimpay et applique la transition correspondante au paiement.
 *
 * Le tableau d'aiguillage est celui de `PaymentStatusAction' sous Payum, a l'identique -- y compris
 * ce qu'il faisait en plus sur l'etat complet : suivre `get-mandate' puis `get-bank-account' pour
 * poser `reference' et `bank_account' dans les `details'. C'est de ces deux cles que
 * `SepaMandate::makeFromPayment()' tire le RUM, le BIC et l'IBAN ; sans elles l'export Magellan ne
 * cree pas la reference bancaire.
 */
final readonly class OrderStateTransitionProcessor implements PaymentTransitionProcessorInterface
{
    private const TRANSITIONS = [
        Constants::ORDER_STATE_ABORT => PaymentTransitions::TRANSITION_CANCEL,
        Constants::ORDER_STATE_ABORT_BY_CLIENT => PaymentTransitions::TRANSITION_CANCEL,
        Constants::ORDER_STATE_ABORT_BY_SERVER => PaymentTransitions::TRANSITION_FAIL,
        Constants::ORDER_STATE_COMPLETE => PaymentTransitions::TRANSITION_COMPLETE,
    ];

    public function __construct(
        private ClientFactoryInterface $clientFactory,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function process(PaymentRequestInterface $paymentRequest): void
    {
        /** @var PaymentInterface $payment */
        $payment = $paymentRequest->getPayment();
        $details = $payment->getDetails();

        $serializedOrder = $details['order'] ?? null;
        if (null === $serializedOrder) {
            return;
        }

        $api = $this->clientFactory->createFromPaymentMethod($paymentRequest->getMethod());
        $order = ResourceSerializer::unserializeResource($serializedOrder);
        if (false === $order instanceof Resource) {
            return;
        }

        $order = $api->getOrder($order->getState()['id']);
        $details['order'] = ResourceSerializer::serializeResource($order);

        $etat = $order->getState()['state'] ?? null;

        if (Constants::ORDER_STATE_COMPLETE === $etat) {
            $details = array_merge($details, $this->providePaymentReference($api, $order));
        }

        $payment->setDetails($details);

        $transition = self::TRANSITIONS[$etat] ?? null;
        if (null === $transition) {
            // `open.running' et les etats suspendus laissent le paiement en attente : Slimpay
            // n'a pas tranche, il n'y a rien a appliquer.
            return;
        }

        if ($this->stateMachine->can($payment, PaymentTransitions::GRAPH, $transition)) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $transition);
        }
    }

    /**
     * Reprend `GetOrderPaymentReferenceAction'. Une carte n'a pas de compte bancaire : on ne suit
     * alors que l'alias.
     *
     * @return array<string, string>
     */
    private function providePaymentReference(Api $api, Resource $order): array
    {
        $estCarte = Constants::PAYMENT_SCHEME_CARD === ($order->getState()['paymentScheme'] ?? null);

        $reference = $api->getOrderPaymentReference(
            $order,
            $estCarte ? Constants::FOLLOW_GET_CARD_ALIAS : Constants::FOLLOW_GET_MANDATE,
        );

        $details = ['reference' => ResourceSerializer::serializeResource($reference)];

        if (false === $estCarte) {
            $details['bank_account'] = ResourceSerializer::serializeResource(
                $api->getOrderBankAccount($reference, Constants::FOLLOW_GET_BANK_ACCOUNT),
            );
        }

        return $details;
    }
}
