<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Processor;

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
 * Le tableau d'aiguillage est celui de `PaymentStatusAction' sous Payum, a l'identique. Ce qui
 * change est la sortie : une transition de la machine a etats de Sylius plutot qu'un `mark*()'
 * sur une requete Payum, et une relecture explicite plutot qu'un effet de bord d'une requete
 * `SyncOrder' imbriquee.
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
        $payment->setDetails($details);

        $transition = self::TRANSITIONS[$order->getState()['state']] ?? null;
        if (null === $transition) {
            // `open.running' et les etats suspendus laissent le paiement en attente : Slimpay
            // n'a pas tranche, il n'y a rien a appliquer.
            return;
        }

        if ($this->stateMachine->can($payment, PaymentTransitions::GRAPH, $transition)) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $transition);
        }
    }
}
