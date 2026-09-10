<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\OrderPay\Provider;

use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transforme l'URL rendue par la capture en redirection vers Slimpay.
 *
 * Sous Payum, `CheckoutRedirectAction' obtenait le meme resultat en levant une exception
 * `HttpRedirect' depuis le coeur du traitement.
 */
final readonly class CaptureHttpResponseProvider implements HttpResponseProviderInterface
{
    public function supports(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): bool {
        return PaymentRequestInterface::STATE_PROCESSING === $paymentRequest->getState();
    }

    public function getResponse(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): Response {
        $data = $paymentRequest->getResponseData();

        /** @var string|null $url */
        $url = $data['url'] ?? null;
        if (null === $url) {
            throw new \LogicException('The Slimpay checkout "url" has not been provided.');
        }

        return new RedirectResponse($url, Response::HTTP_SEE_OTHER);
    }
}
