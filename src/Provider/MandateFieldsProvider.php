<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Construit les champs de mandat attendus par Slimpay a partir de la commande.
 *
 * Reprend le mappage de `SyliusConvertAction' puis `SignMandateAction' sous Payum, a l'identique.
 * Trois champs meritent d'etre explicites, parce qu'ils ne se devinent pas :
 *
 *   - `telephone', `companyName' et `organizationName' partent a `null'. Sous Payum, ils lisaient
 *     `$model['phone']', `$model['company']' et `$model['organization']', qu'aucune action ne
 *     posait jamais : l'ArrayObject rendait donc null. Les alimenter fait rejeter la commande --
 *     Slimpay repond 400 code 142 « Invalid companyName property » sur une chaine vide.
 *   - `street2' recoit la societe de l'adresse de facturation, pas une seconde ligne d'adresse.
 *     C'est ce que faisait `$model['address2'] = $address->getCompany() ?? ''`.
 */
final readonly class MandateFieldsProvider implements MandateFieldsProviderInterface
{
    public function provide(PaymentInterface $payment): array
    {
        $order = $payment->getOrder();
        $customer = $order?->getCustomer();
        $address = $order?->getBillingAddress();

        return [
            'givenName' => $address?->getFirstName(),
            'familyName' => $address?->getLastName(),
            'email' => $customer?->getEmail(),
            'telephone' => null,
            'companyName' => null,
            'organizationName' => null,
            'billingAddress' => [
                'street1' => $address?->getStreet(),
                'street2' => $address?->getCompany() ?? '',
                'city' => $address?->getCity(),
                'postalCode' => $address?->getPostcode(),
                'country' => $address?->getCountryCode(),
            ],
        ];
    }

    public function provideSubscriberReference(PaymentInterface $payment): ?string
    {
        $customerId = $payment->getOrder()?->getCustomer()?->getId();

        return null === $customerId ? null : (string) $customerId;
    }
}
