<?php declare(strict_types=1);

namespace App\Twig;

use PHP_SF\System\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the PHP_SF global helper functions to Twig templates, so `.html.twig`
 * views rendered from PHP_SF controllers have the same helpers available as
 * plain-PHP class views (`<?= pageTitle() ?>` becomes `{{ pageTitle() }}`).
 */
final class PhpSfHelpersExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pageTitle', 'pageTitle'),
            new TwigFunction('csrf_token', 'csrf_token'),
            new TwigFunction('manifest_asset', 'manifest_asset'),
            new TwigFunction('manifest_has', 'manifest_has'),
            new TwigFunction('_t', '_t'),
            new TwigFunction('route_link', [Router::class, 'getRouteLink']),
        ];
    }
}
