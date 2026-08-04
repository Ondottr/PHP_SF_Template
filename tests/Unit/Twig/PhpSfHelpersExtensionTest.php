<?php declare(strict_types=1);

namespace Tests\Unit\Twig;

use App\Twig\PhpSfHelpersExtension;
use Codeception\Test\Unit;
use Twig\TwigFunction;

/**
 * Pure-unit: pins the full set of PHP_SF helpers exposed to Twig templates.
 *
 * Twig resolves string callables lazily at render time, so a registration typo
 * or a dropped entry would ship green through CI and fail only when the first
 * real template calls the helper.
 */
class PhpSfHelpersExtensionTest extends Unit
{
    public function testAllHelpersAreExposedWithExpectedCallables(): void
    {
        $expected = [
            'pageTitle' => 'pageTitle',
            // Intentional override of the Symfony bridge's csrf_token(): PHP_SF's
            // global wins in the shared Twig environment (see the extension docblock)
            'csrf_token' => 'csrf_token',
            'manifest_asset' => 'manifest_asset',
            'manifest_has' => 'manifest_has',
            '_t' => '_t',
            'route_link' => 'routeLink',
        ];

        $registered = [];

        foreach ((new PhpSfHelpersExtension())->getFunctions() as $function) {
            $this->assertInstanceOf(TwigFunction::class, $function);
            $registered[$function->getName()] = $function->getCallable();
        }

        foreach ($expected as $name => $callable) {
            $this->assertArrayHasKey($name, $registered);
            $this->assertSame($callable, $registered[$name]);
        }

        $this->assertCount(\count($expected), $registered);
    }
}
