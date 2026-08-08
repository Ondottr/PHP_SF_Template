<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\View\blank_page;
use PHP_SF\System\Classes\Abstracts\Middleware;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Kernel;
use Symfony\Component\HttpFoundation\JsonResponse;

final class blank extends Middleware
{
    protected function result(): bool|JsonResponse|RedirectResponse
    {
        Kernel::setFooterTemplateClassName(blank_page::class);
        Kernel::setHeaderTemplateClassName(blank_page::class);

        return true;
    }
}
