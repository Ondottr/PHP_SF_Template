<?php declare( strict_types=1 );

namespace App\Http\Middleware;

use App\View\blank_page;
use PHP_SF\System\Classes\Abstracts\Middleware;
use PHP_SF\System\Core\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

final class blank extends Middleware
{

    protected function result(): bool|JsonResponse|RedirectResponse
    {
        $this->changeFooterTemplateClassName( blank_page::class );
        $this->changeHeaderTemplateClassName( blank_page::class );

        return true;
    }

}
