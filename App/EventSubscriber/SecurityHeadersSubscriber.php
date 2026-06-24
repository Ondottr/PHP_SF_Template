<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to Symfony's response lifecycle and applies security-related HTTP headers.
 *
 * Purpose:
 * - Centralizes browser hardening headers in one place instead of repeating them in controllers.
 * - Enforces baseline protections for every main HTTP response produced by the application.
 *
 * Current behavior:
 * - Listens to {@see KernelEvents::RESPONSE} via {@see EventSubscriberInterface}.
 * - Runs only for the main request ({@see KernelEvent::isMainRequest()}), skipping sub-requests.
 * - Sets:
 *   - `X-Frame-Options: DENY` to block clickjacking through framing.
 *   - `X-Content-Type-Options: nosniff` to prevent MIME type sniffing.
 *   - `Referrer-Policy: strict-origin-when-cross-origin` to reduce referrer leakage.
 *
 * Optional behavior already scaffolded in this class:
 * - Strict-Transport-Security (HSTS) header for HTTPS-only enforcement.
 * - Content-Security-Policy (CSP) header generation through `buildCsp()`.
 * - CSP nonce generation and propagation to redirect inline scripts
 *   (e.g. framework `history.replaceState()` handling on redirects).
 *
 * How it works:
 * - Symfony dispatches the response event.
 * - The subscriber receives {@see ResponseEvent}, retrieves the {@see Response}, and mutates headers.
 * - Optional CSP/HSTS logic can be enabled by uncommenting the prepared code paths and
 *   adjusting directives to match frontend/runtime requirements.
 */
final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        /**
         * Uncomment to enable CSP with nonce support.
         *
         * The nonce is passed to {@see RedirectResponse} so the framework's inline
         * `history.replaceState()` script gets the nonce attribute automatically.
         * Without this, a strict script-src policy will block that script and
         * redirect URLs will stop updating in the browser address bar.
         */
        //        if ( self::$nonce === null ) {
        //            self::$nonce = base64_encode( random_bytes( 16 ) );
        //            RedirectResponse::setCspNonce( self::$nonce );
        //        }

        $response = $event->getResponse();
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        /**
         * HSTS tells browsers to always use HTTPS for this domain.
         *
         * Add `preload` only after submitting the domain to the HSTS preload list.
         * Do not enable this on a domain that may need to serve plain HTTP in the future,
         * because browsers cache the policy for `max-age` seconds and it is not easily cleared.
         */
        //        $response->headers->set( 'Strict-Transport-Security', 'max-age=31536000; includeSubDomains' );

        // Uncomment once you've configured buildCsp() below.
        //        $response->headers->set( 'Content-Security-Policy', $this->buildCsp() );
    }

    /**
     * CSP nonce shared within the same PHP process.
     *
     * KernelEvents::RESPONSE can fire twice for RedirectResponse
     * (original request and inner {@see Router::init()} re-route),
     * so the null guard keeps a stable nonce value per HTTP request and
     * prevents generating multiple nonces in the same request lifecycle.
     */
    //    private static ?string $nonce = null;

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    /**
     * Builds the Content-Security-Policy header value.
     * Adjust directives to match your application's needs before enabling.
     *
     * script-src: 'nonce-...' allows only scripts that carry the matching nonce attribute.
     * The framework's {@see RedirectResponse} inline script receives the nonce automatically
     * via {@see RedirectResponse::setCspNonce()} — uncomment the nonce block above first.
     *
     * style-src: 'unsafe-inline' is required for Bootstrap's runtime style injections.
     * Replace with a nonce or hashes if you control all inline styles.
     *
     * connect-src: add any external API origins your JS calls (e.g. 'https://api.example.com').
     *
     * upgrade-insecure-requests: instructs browsers to rewrite http:// sub-resource
     * requests to https:// automatically. Safe to enable on full-HTTPS deployments.
     */
    //    private function buildCsp(): string
    //    {
    //        $directives = [
    //            "default-src 'self'",
    //            "script-src 'self' 'nonce-" . self::$nonce . "'",
    //            "style-src 'self' 'unsafe-inline'",
    //            "img-src 'self' data:",
    //            "font-src 'self'",
    //            "connect-src 'self'",
    //            "object-src 'none'",
    //            "base-uri 'self'",
    //            "form-action 'self'",
    //            "frame-ancestors 'none'",
    //            'upgrade-insecure-requests',
    //        ];
    //
    //        return implode( '; ', $directives );
    //    }
}
