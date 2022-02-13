<?php declare( strict_types=1 );

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ExampleSymfonyController extends AbstractController
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
