<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Construit les champs de mandat attendus par Slimpay a partir de la commande.
 *
 * Reprend a l'identique ce que faisaient `SyliusConvertAction' puis `SignMandateAction' sous Payum :
 * l'adresse de facturation alimente l'adresse du mandat, le client fournit la reference abonne.
 * La difference est qu'il n'y a plus d'aller-retour par un modele tableau partage entre actions.
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
            'telephone' => $address?->getPhoneNumber(),
            'companyName' => $address?->getCompany(),
            'organizationName' => $address?->getCompany(),
            'billingAddress' => [
                'street1' => $address?->getStreet(),
                'street2' => '',
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
