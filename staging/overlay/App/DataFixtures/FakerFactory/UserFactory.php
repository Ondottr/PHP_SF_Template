<?php declare(strict_types=1);

namespace App\DataFixtures\FakerFactory;

use App\Entity\Main\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array
    {
        $faker = self::faker();

        return [
            'email' => $faker->unique()->safeEmail(),
            'password' => $faker->password(8, 20),
            'colText' => $faker->optional(0.7)->sentence(10),
            'colInteger' => $faker->optional()->numberBetween(-2147483648, 2147483647),
            'colSmallint' => $faker->optional()->numberBetween(-32768, 32767),
            'colBigint' => $faker->optional()->numberBetween(0, PHP_INT_MAX),
            'colBoolean' => $faker->optional()->boolean(),
            'colDecimal' => $faker->optional()->numerify('####.####'),
            'colFloat' => $faker->optional()->randomFloat(4, -1000, 1000),
            'colDatetimetz' => $faker->optional()->dateTimeBetween('-5 years', 'now'),
            'colDate' => $faker->optional()->dateTimeBetween('-5 years', 'now'),
            'colTime' => $faker->optional(0.8)->dateTime(),
            'colJson' => $faker->boolean() ? [
                'role' => $faker->word(),
                'theme' => $faker->colorName(),
            ] : null,
            'colBlob' => $faker->optional(0.5)->text(50),
            'colGuid' => $faker->optional()->uuid(),
            'colArray' => $faker->optional()->words(3),
            'colSimpleArray' => $faker->optional()->words(3),
            'colBinary' => $faker->optional(0.5)->regexify('[a-zA-Z0-9]{15}'),
        ];
    }
}
