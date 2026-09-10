<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandHandler;

use Akki\SyliusSlimpayPlugin\Command\StatusPaymentRequest;
use Akki\SyliusSlimpayPlugin\Processor\PaymentTransitionProcessorInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Relit l'etat de la souscription chez Slimpay au retour du client, et fait avancer le paiement.
 *
 * Remplace `OrderStatusAction' et `PaymentStatusAction', qui portaient le meme aiguillage d'etats
 * mais le rendaient par des `mark*()' sur une requete Payum.
 */
#[AsMessageHandler(bus: 'sylius.payment_request.command_bus')]
final readonly class StatusPaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private PaymentTransitionProcessorInterface $paymentTransitionProcessor,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(StatusPaymentRequest $statusPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($statusPaymentRequest);

        $this->paymentTransitionProcessor->process($paymentRequest);

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );
    }
}
