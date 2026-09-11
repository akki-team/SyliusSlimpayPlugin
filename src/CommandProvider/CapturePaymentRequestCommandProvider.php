<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandProvider;

use Akki\SyliusSlimpayPlugin\Command\CaptureEndPaymentRequest;
use Akki\SyliusSlimpayPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

/**
 * Une demande deja en `processing' n'ouvre pas un second ordre Slimpay : elle relit celui qui
 * existe. C'est ce qui permet de reprendre une signature de mandat abandonnee, et de rendre la
 * demande finale quand l'ordre a ete abandonne cote Slimpay -- sans quoi un nouveau clic sur payer
 * redirigerait indefiniment vers une URL morte.
 */
final class CapturePaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return PaymentRequestInterface::ACTION_CAPTURE === $paymentRequest->getAction();
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        if (PaymentRequestInterface::STATE_PROCESSING === $paymentRequest->getState()) {
            return new CaptureEndPaymentRequest($paymentRequest->getId());
        }

        return new CapturePaymentRequest($paymentRequest->getId());
    }
}
