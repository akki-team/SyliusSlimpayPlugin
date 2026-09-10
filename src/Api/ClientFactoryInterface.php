<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Api;

use Sylius\Component\Payment\Model\PaymentMethodInterface;

interface ClientFactoryInterface
{
    public function createFromPaymentMethod(PaymentMethodInterface $paymentMethod): Api;
}
