<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class TemplateEnginesCest
{
    private const CHROME_HEADER_MARKER = 'header header_elements row';

    private const CHROME_FOOTER_MARKER = '<div class="footer">';


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
        $I->seeInSource(self::CHROME_HEADER_MARKER);
        $I->seeInSource(self::CHROME_FOOTER_MARKER);

        // Engine output wrapped like class views
        $I->seeInSource('<div class="engines_demo">');
        $I->seeInSource('Rendered by Twig');

        // PHP_SF helper functions exposed to Twig
        $I->seeInSource('twig-csrf-token');
        $I->seeInSource('href="/"');
    }

    public function testTwigStandalonePageSkipsLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/twig/standalone');
        $I->seeResponseCodeIs(200);

        $I->seeInSource('<!DOCTYPE html>');
        $I->seeInSource('Standalone Twig page');

        // Framework chrome skipped — the template provides its own document
        $I->dontSeeInSource(self::CHROME_HEADER_MARKER);
        $I->dontSeeInSource(self::CHROME_FOOTER_MARKER);
        $I->dontSeeInSource('<div class="engines_standalone">');
    }

    public function testBladeFragmentRendersInsideLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/blade');
        $I->seeResponseCodeIs(200);

        // Chrome present
        $I->seeInSource(self::CHROME_HEADER_MARKER);
        $I->seeInSource(self::CHROME_FOOTER_MARKER);

        // Engine output wrapped like class views
        $I->seeInSource('<div class="engines_demo">');
        $I->seeInSource('Rendered by Blade');

        // @csrf wired to the framework session token
        $I->seeInSource("<input type='hidden' name='_token'");
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
