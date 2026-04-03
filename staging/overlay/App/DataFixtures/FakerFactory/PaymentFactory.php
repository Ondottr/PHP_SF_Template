<?php declare(strict_types=1);

namespace App\DataFixtures\FakerFactory;

use App\Entity\Payments\Payment;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Payment>
 */
final class PaymentFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Payment::class;
    }

    protected function defaults(): array
    {
        $faker = self::faker();

        return [
            'amount' => $faker->numerify('###.##'),
            'currency' => $faker->randomElement(['USD', 'EUR', 'GBP']),
            'status' => $faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'colText' => $faker->optional(0.7)->sentence(12),
            'colInteger' => $faker->optional()->numberBetween(-2147483648, 2147483647),
            'colSmallint' => $faker->optional()->numberBetween(-32768, 32767),
            'colBigint' => $faker->optional()->numberBetween(0, PHP_INT_MAX),
            'colBoolean' => $faker->optional()->boolean(),
            'colDecimal' => $faker->optional()->numerify('####.####'),
            'colFloat' => $faker->optional()->randomFloat(4, -1000, 1000),
            'colDate' => $faker->optional()->dateTimeBetween('-5 years', 'now'),
            'colTime' => $faker->optional(0.8)->dateTime(),
            'colJson' => $faker->optional()->passthrough([
                'gateway' => $faker->word(),
                'txn_id' => $faker->uuid(),
            ]),
            'colBlob' => $faker->optional(0.5)->text(50),
            'colGuid' => $faker->optional()->uuid(),
            'colArray' => $faker->optional()->words(3),
            'colSimpleArray' => $faker->optional()->words(3),
            'colBinary' => $faker->optional(0.5)->regexify('[a-zA-Z0-9]{15}'),
        ];
    }
}
