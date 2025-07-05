<?php declare(strict_types=1);

namespace App\Enums\Amqp;

use PHP_SF\System\Database\RabbitMQ;
use PHP_SF\System\Database\RabbitMQConsumer;

/**
 * Class QueueEnum
 *
 * @package App\Enums\Amqp
 * @author  Dmytro Dyvulskyi <dmytro.dyvulskyi@nations-original.com>
 */
enum QueueEnum: string
{

    case DEFAULT = 'default_queue';
    // todo: add more queues, if needed


    public function getMessageBus(): RabbitMQ
    {
        return RabbitMQ::getInstance($this);
    }

    public function consume(callable $callback): void
    {
        (new RabbitMQConsumer())
            ->consume($this, $callback);

    }

}
