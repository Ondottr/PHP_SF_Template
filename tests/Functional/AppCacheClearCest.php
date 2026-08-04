<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class AppCacheClearCest
{
    public function testCacheClearRemovesCompiledBladeFiles(FunctionalTester $I): void
    {
        $bladeCacheDir = codecept_root_dir() . 'var/cache/bladeone';
        @mkdir($bladeCacheDir, 0777, true);

        $sentinel = $bladeCacheDir . '/review_sentinel.php';
        file_put_contents($sentinel, '<?php // sentinel — must be removed by app:cache:clear');

        $I->runSymfonyConsoleCommand('app:cache:clear');

        $I->assertFileDoesNotExist($sentinel);
    }
}
