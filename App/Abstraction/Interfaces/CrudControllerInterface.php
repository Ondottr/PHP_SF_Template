<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

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
