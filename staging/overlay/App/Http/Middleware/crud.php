<?php declare( strict_types=1 );

namespace App\Http\Middleware;

use App\View\Crud\Layout\footer;
use App\View\Crud\Layout\header;
use PHP_SF\System\Classes\Abstracts\Middleware;
use PHP_SF\System\Core\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

final class crud extends Middleware
{

    protected function result(): bool|JsonResponse|RedirectResponse
    {
        $this->changeFooterTemplateClassName( header::class );
        $this->changeHeaderTemplateClassName( footer::class );

        return true;
    }

}
