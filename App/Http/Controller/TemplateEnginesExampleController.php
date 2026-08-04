<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace App\Http\Controller;

use App\View\engines_mixed_page;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\Response;

final class TemplateEnginesExampleController extends AbstractController
{
    #[Route(url: '/example/twig', httpMethod: 'GET')]
    public function twig_page(): Response
    {
        return $this->render('example/engines_demo.html.twig', [
            'engine' => 'Twig',
        ]);
    }

    #[Route(url: '/example/twig/standalone', httpMethod: 'GET')]
    public function twig_standalone_page(): Response
    {
        return $this->render(
            'example/engines_standalone.html.twig',
            ['engine' => 'Twig'],
            useLayout: false,
        );
    }

    #[Route(url: '/example/blade', httpMethod: 'GET')]
    public function blade_page(): Response
    {
        return $this->render('example/engines_demo.blade.php', [
            'engine' => 'Blade',
        ]);
    }

    #[Route(url: '/example/mixed', httpMethod: 'GET')]
    public function mixed_page(): Response
    {
        return $this->render(engines_mixed_page::class);
    }
}
