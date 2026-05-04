<?php

namespace Tests\Unit;

use PHP_SF\System\Classes\Helpers\CursorPaginationResult;
use PHP_SF\System\Core\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tests\Support\Uni2Tester;

class ApiResponseCest
{

    public function testExtendsJsonResponse(Uni2Tester $I): void
    {
        $I->assertInstanceOf(JsonResponse::class, ApiResponse::success());
    }

    public function testSuccessBodyStructure(Uni2Tester $I): void
    {
        $r    = ApiResponse::success(['key' => 'value']);
        $body = json_decode($r->getContent(), associative: true);

        $I->assertTrue($body['success']);
        $I->assertSame(['key' => 'value'], $body['data']);
        $I->assertNull($body['errors']);
        $I->assertArrayHasKey('timestamp', $body['meta']);
        $I->assertNull($body['meta']['pagination']);
    }

    public function testErrorBodyStructure(Uni2Tester $I): void
    {
        $r    = ApiResponse::error('Oops', 422);
        $body = json_decode($r->getContent(), associative: true);

        $I->assertFalse($body['success']);
        $I->assertNull($body['data']);
        $I->assertSame(['Oops'], $body['errors']);
        $I->assertSame(422, $r->getStatusCode());
    }

    public function testPaginatedResponseContainsMeta(Uni2Tester $I): void
    {
        $result = new CursorPaginationResult(
            items:      [['id' => 1], ['id' => 2]],
            cursor:     null,
            nextCursor: 'eyJpZCI6Mn0=',
            prevCursor: null,
            perPage:    20,
            hasMore:    true,
        );

        $r    = ApiResponse::success(data: [['id' => 1], ['id' => 2]], pagination: $result);
        $body = json_decode($r->getContent(), associative: true);

        $I->assertNotNull($body['meta']['pagination']);
        $I->assertSame('eyJpZCI6Mn0=', $body['meta']['pagination']['next_cursor']);
        $I->assertTrue($body['meta']['pagination']['has_more']);
        $I->assertSame(20, $body['meta']['pagination']['per_page']);
    }

    public function testCreatedIs201(Uni2Tester $I): void
    {
        $I->assertSame(201, ApiResponse::created()->getStatusCode());
    }

    public function testNotFoundIs404(Uni2Tester $I): void
    {
        $I->assertSame(404, ApiResponse::notFound('Not here.')->getStatusCode());
    }

}
