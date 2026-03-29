<?php declare( strict_types=1 );

namespace App\Http\SymfonyControllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ExampleSymfonyController
{

    #[Route( '/example/symfony', name: 'example_symfony' )]
    public function index(): JsonResponse
    {
        return new JsonResponse(
            [
                'key' => 'value',
            ], JsonResponse::HTTP_OK
        );
    }

}
