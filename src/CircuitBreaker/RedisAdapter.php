<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/12
 * Time: 11:20
 */

namespace ESD\Plugins\CircuitBreaker;


use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Configuration;
use Ackintosh\Ganesha\Exception\StorageException;
use Ackintosh\Ganesha\Storage\Adapter\SlidingTimeWindowInterface;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use ESD\Plugins\Redis\GetRedis;

class RedisAdapter implements AdapterInterface, SlidingTimeWindowInterface
{
    use GetRedis;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Configuration $configuration
     *
     * @return void
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $service
     *
     * @return int
     * @throws StorageException
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function load($service)
    {
        $expires = microtime(true) - $this->configuration['timeWindow'];

        if ($this->redis($this->configuration['db'])->zRemRangeByScore($service, '-inf', $expires) === false) {
            throw new StorageException('Failed to remove expired elements. service: ' . $service);
        }

        $r = $this->redis($this->configuration['db'])->zCard($service);

        if ($r === false) {
            throw new StorageException('Failed to load cardinality. service: ' . $service);
        }

        return $r;
    }

    public function save($resouce, $count)
    {
        // Redis adapter does not support Count strategy
    }

    /**
     * @param string $service
     *
     * @throws StorageException
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function increment($service)
    {
        $t = microtime(true);
        $r = $this->redis($this->configuration['db'])->zAdd($service, $t, $t);

        if ($r === false) {
            throw new StorageException('Failed to add sorted set. service: ' . $service . ', returned: ' . print_r($r, true));
        }
    }

    public function decrement($service)
    {
        // Redis adapter does not support Count strategy
    }

    public function saveLastFailureTime($service, $lastFailureTime)
    {
        // nop
    }

    /**
     * @param $service
     *
     * @return int|void
     * @throws StorageException
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function loadLastFailureTime($service)
    {
        $lastFailure = $this->redis($this->configuration['db'])->zRange($service, -1, -1);

        if (!$lastFailure) {
            return;
        }

        return (int)$lastFailure[0];
    }

    /**
     * @param string $service
     * @param int $status
     *
     * @throws StorageException
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function saveStatus($service, $status)
    {
        $r = $this->redis($this->configuration['db'])->set($service, $status);

        if ($r === false) {
            throw new StorageException(sprintf(
                'Failed to save status. service: %s, status: %d',
                $service,
                $status
            ));
        }
    }

    /**
     * @param string $service
     *
     * @return int
     * @throws StorageException
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function loadStatus($service)
    {
        $r = $this->redis($this->configuration['db'])->get($service);

        // \Redis::get() returns FALSE if key didn't exist.
        // @see https://github.com/phpredis/phpredis#get
        if ($r === false) {
            $this->saveStatus($service, Ganesha::STATUS_CALMED_DOWN);
            return Ganesha::STATUS_CALMED_DOWN;
        }

        return (int)$r;
    }

    public function reset()
    {
        // TODO: Implement reset() method.
    }
}