<?php declare(strict_types=1);

namespace App\Http\Middleware;

use PHP_SF\System\Classes\Abstracts\Middleware;
use Symfony\Component\HttpFoundation\JsonResponse;


final class example_middleware extends Middleware
{

    public function result(): bool|JsonResponse
    {
        return true;
    }

}
