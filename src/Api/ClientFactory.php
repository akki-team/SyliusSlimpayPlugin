<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Api;

use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

/**
 * Construit le client Slimpay a partir de la passerelle du moyen de paiement.
 *
 * C'est ce qui fait disparaitre le `findOneBy(['code' => 'slimpay'])' code en dur du contrôleur de
 * notification Payum : le canal arrive par la methode de paiement, il n'y a plus a le deviner. Les
 * crediteurs different d'un canal a l'autre -- `disneypresse' pour DSN, `tbsfleuruspresse' pour les
 * deux autres.
 */
final readonly class ClientFactory implements ClientFactoryInterface
{
    public function createFromPaymentMethod(PaymentMethodInterface $paymentMethod): Api
    {
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig, sprintf(
            'The payment method (code: %s) has not been configured.',
            (string) $paymentMethod->getCode(),
        ));

        return new Api($gatewayConfig->getConfig());
    }
}
