<?php declare( strict_types=1 );
/**
 * Created by PhpStorm.
 * User: ondottr
 * Date: 15/02/2023
 * Time: 6:43 pm
 */

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