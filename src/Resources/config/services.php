<?php

declare(strict_types=1);

use Akki\SyliusSlimpayPlugin\CommandProvider\CapturePaymentRequestCommandProvider;
use Akki\SyliusSlimpayPlugin\CommandProvider\StatusPaymentRequestCommandProvider;
use Akki\SyliusSlimpayPlugin\OrderPay\Provider\CaptureHttpResponseProvider;
use Sylius\Bundle\PaymentBundle\CommandProvider\ActionsCommandProvider;
use Sylius\Bundle\PaymentBundle\Provider\ActionsHttpResponseProvider;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        // Portee au greffon : poser un alias sur UrlProviderInterface le rendrait global, or
        // `sylius_shop.controller.payment_request_pay' s'en sert aussi.
        //
        // C'est `/pay/{hash}' et non l'apres-paiement : au retour de Slimpay, la demande de
        // capture est ainsi relue par CaptureEnd, qui la finalise. Sans ca elle resterait en
        // `processing' a vie. PaymentRequestPayAction bascule ensuite sur l'apres-paiement.
        ->bind(
            'Sylius\Bundle\CoreBundle\OrderPay\Provider\UrlProviderInterface $payUrlProvider',
            service('sylius_shop.provider.order_pay.payment_request_pay_url'),
        )
    ;

    // Les attributs font le reste : #[AsGatewayConfigurationType] sur le formulaire et
    // #[AsMessageHandler] sur les gestionnaires.
    $services->load('Akki\\SyliusSlimpayPlugin\\', __DIR__ . '/../../*')
        ->exclude([
            __DIR__ . '/../../{Command,Constants,DependencyInjection,Resources,Util}',
            __DIR__ . '/../../AkkiSyliusSlimpayPlugin.php',
        ])
    ;

    // Aiguillage par action pour la factory `slimpay'. C'est ce que Sylius attend : un
    // ActionsCommandProvider indexe par action, lui-meme tague par nom de passerelle. Sans ce
    // tag, GatewayFactoryCommandProvider ne trouve rien et la demande de paiement echoue.
    $services->set('akki.slimpay.command_provider', ActionsCommandProvider::class)
        ->args([
            tagged_locator('akki.slimpay.command_provider', 'action'),
        ])
        ->tag('sylius.payment_request.command_provider', ['gateway_factory' => 'slimpay']);

    $services->set('akki.slimpay.command_provider.capture', CapturePaymentRequestCommandProvider::class)
        ->tag('akki.slimpay.command_provider', ['action' => PaymentRequestInterface::ACTION_CAPTURE])
        ->tag('akki.slimpay.command_provider', ['action' => PaymentRequestInterface::ACTION_AUTHORIZE]);

    $services->set('akki.slimpay.command_provider.status', StatusPaymentRequestCommandProvider::class)
        ->tag('akki.slimpay.command_provider', ['action' => PaymentRequestInterface::ACTION_STATUS]);

    $services->set('akki.slimpay.provider.http_response', ActionsHttpResponseProvider::class)
        ->args([
            tagged_locator('akki.slimpay.provider.http_response', 'action'),
        ])
        ->tag('sylius.payment_request.provider.http_response', ['gateway_factory' => 'slimpay']);

    $services->set('akki.slimpay.provider.http_response.capture', CaptureHttpResponseProvider::class)
        ->tag('akki.slimpay.provider.http_response', ['action' => PaymentRequestInterface::ACTION_CAPTURE])
        ->tag('akki.slimpay.provider.http_response', ['action' => PaymentRequestInterface::ACTION_AUTHORIZE]);
};
