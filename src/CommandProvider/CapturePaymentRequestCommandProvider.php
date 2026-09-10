<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandProvider;

use Akki\SyliusSlimpayPlugin\Command\CapturePaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

final class CapturePaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return PaymentRequestInterface::ACTION_CAPTURE === $paymentRequest->getAction();
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        return new CapturePaymentRequest($paymentRequest->getId());
    }
}
