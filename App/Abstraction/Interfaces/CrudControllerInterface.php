<?php declare( strict_types=1 );

namespace App\Abstraction\Interfaces;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Interface CrudControllerInterface
 *
 * Defines the standard CRUD operations to be implemented by a CRUD controller.
 *
 * @package App\Abstraction\Interfaces
 * @author  Dmytro Dyvulskyi <dmytro.dyvulskyi@nations-original.com>
 */
interface CrudControllerInterface
{

    /**
     * Create a new resource.
     *
     * @method POST
     *
     * @return JsonResponse {@see JsonResponse::HTTP_CREATED} with the created resource
     */
    public function create(): JsonResponse;

    /**
     * Read a specific resource by its identifier.
     *
     * @method GET
     *
     * @param int $id Identifier of the resource
     *
     * @return JsonResponse {@see JsonResponse::HTTP_OK} with the requested resources
     */
    public function read( int $id ): JsonResponse;

    /**
     * Read all resources.
     *
     * @method GET
     *
     * @return JsonResponse {@see JsonResponse::HTTP_OK} with all the resources
     */
    public function readAll(): JsonResponse;

    /**
     * Update an existing resource identified by its identifier.
     *
     * @method PATCH
     *
     * @param int $id Identifier of the resource
     *
     * @return JsonResponse {@see JsonResponse::HTTP_OK} with the updated resource
     */
    public function update( int $id ): JsonResponse;

    /**
     * Replace an existing resource identified by its identifier.
     *
     * @method PUT
     *
     * @param int $id Identifier of the resource
     *
     * @return JsonResponse {@see JsonResponse::HTTP_OK} with the replaced resource
     */
    public function replace( int $id ): JsonResponse;

    /**
     * Delete a specific resource by its identifier.
     *
     * @method DELETE
     *
     * @param int $id Identifier of the resource
     *
     * @return JsonResponse {@see JsonResponse::HTTP_NO_CONTENT} in case of success
     */
    public function delete( int $id ): JsonResponse;

}
