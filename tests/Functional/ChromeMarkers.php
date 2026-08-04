<?php declare(strict_types=1);

namespace Tests\Functional;

/**
 * Layout chrome markers shared by functional Cests — keep in sync with
 * PHP_SF\Templates\Layout\header.php / footer.php markup.
 */
final class ChromeMarkers
{
    public const HEADER = 'header header_elements row';

    public const FOOTER = '<div class="footer">';
}
