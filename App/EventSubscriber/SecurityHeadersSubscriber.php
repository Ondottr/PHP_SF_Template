<?php declare( strict_types=1 );

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::RESPONSE => 'onKernelResponse' ];
    }

    public function onKernelResponse( ResponseEvent $event ): void
    {
        if ( !$event->isMainRequest() ) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set( 'X-Frame-Options', 'DENY' );
        $response->headers->set( 'X-Content-Type-Options', 'nosniff' );
        $response->headers->set( 'Referrer-Policy', 'strict-origin-when-cross-origin' );
    }
}