<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace App\Http\Controller;

use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Attributes\RouteApi;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Demonstrates explicit API endpoint declaration with the #[RouteApi] attribute.
 *
 * The class-level attribute marks every route of this controller as an API endpoint:
 * responses skip the HTML layout (header/footer), and middleware failures return
 * JSON 403 instead of a redirect.
 */
#[RouteApi]
final class RouteApiExampleController extends AbstractController
{
    #[Route(url: '/example/api/status', httpMethod: 'GET')]
    public function status(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route(url: '/example/api/plain', httpMethod: 'GET')]
    public function plain_response(): Response
    {
        // useLayout is true by default, yet #[RouteApi] suppresses the header/footer
        // chrome — API endpoints never get the HTML layout
        return new Response(renderedContent: 'plain api response without layout');
    }
}
