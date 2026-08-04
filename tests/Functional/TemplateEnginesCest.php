<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class TemplateEnginesCest
{
    public function testClassViewStillRenders(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->seeInSource('<div class="welcome_page">');
    }

    public function testTwigFragmentRendersInsideLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/twig');
        $I->seeResponseCodeIs(200);

        // Chrome present
        $I->seeInSource(ChromeMarkers::HEADER);
        $I->seeInSource(ChromeMarkers::FOOTER);

        // Engine output wrapped like class views
        $I->seeInSource('<div class="engines_demo">');
        $I->seeInSource('Rendered by Twig');

        // PHP_SF helper functions exposed to Twig — markup AND a real token value
        $I->seeInSource('twig-csrf-token');
        $I->assertNotEmpty($I->grabTextFrom('.twig-csrf-token'));
        $I->seeInSource('href="/"');
    }

    public function testTwigStandalonePageSkipsLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/twig/standalone');
        $I->seeResponseCodeIs(200);

        $I->seeInSource('<!DOCTYPE html>');
        $I->seeInSource('Standalone Twig page');

        // Framework chrome skipped — the template provides its own document
        $I->dontSeeInSource(ChromeMarkers::HEADER);
        $I->dontSeeInSource(ChromeMarkers::FOOTER);
        $I->dontSeeInSource('<div class="engines_standalone">');
    }

    public function testBladeFragmentRendersInsideLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/blade');
        $I->seeResponseCodeIs(200);

        // Chrome present
        $I->seeInSource(ChromeMarkers::HEADER);
        $I->seeInSource(ChromeMarkers::FOOTER);

        // Engine output wrapped like class views
        $I->seeInSource('<div class="engines_demo">');
        $I->seeInSource('Rendered by Blade');

        // @csrf wired to the framework session token — input present AND filled
        $I->seeInSource("<input type='hidden' name='_token'");
        $I->assertNotEmpty($I->grabAttributeFrom('.blade-csrf input', 'value'));
    }

    public function testMixedEnginesOnOnePage(FunctionalTester $I): void
    {
        $I->amOnPage('/example/mixed');
        $I->seeResponseCodeIs(200);

        // Plain-PHP class view body
        $I->seeInSource('<div class="engines_mixed_page">');
        $I->seeInSource('Mixed engines on one page');

        // Twig and Blade partials imported from the class view
        $I->seeInSource('twig-partial');
        $I->seeInSource('blade-partial');
    }
}
