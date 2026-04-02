<?php /** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\Controller;

use App\Http\Middleware\crud;
use App\View\crud_home;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\Response;

final class CrudHomeController extends AbstractController
{

    #[Route( url: 'crud', httpMethod: 'GET', name: 'crud_home', middleware: [ crud::class ] )]
    public function index(): Response
    {
        return $this->render( crud_home::class, pageTitle: 'CRUD Home' );
    }

}
