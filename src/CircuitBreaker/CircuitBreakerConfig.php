<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/12
 * Time: 11:26
 */

namespace ESD\Plugins\CircuitBreaker;


use ESD\Core\Plugins\Config\BaseConfig;

class CircuitBreakerConfig extends BaseConfig
{
    const key = "circuit_breaker";
    /**
     * @var string
     */
    protected $redisDb = "default";
    /**
     * @var bool
     */
    protected $enable = true;
    /**
     * @var int
     */
    protected $timeWindow = 30;
    /**
     * @var int
     */
    protected $failureRateThreshold = 50;
    /**
     * @var int
     */
    protected $minimumRequests = 10;
    /**
     * @var int
     */
    protected $intervalToHalfOpen = 5;

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string
     */
    public function getRedisDb(): string
    {
        return $this->redisDb;
    }

    /**
     * @param string $redisDb
     */
    public function setRedisDb(string $redisDb): void
    {
        $this->redisDb = $redisDb;
    }

    /**
     * @return int
     */
    public function getTimeWindow(): int
    {
        return $this->timeWindow;
    }

    /**
     * @param int $timeWindow
     */
    public function setTimeWindow(int $timeWindow): void
    {
        $this->timeWindow = $timeWindow;
    }

    /**
     * @return int
     */
    public function getFailureRateThreshold(): int
    {
        return $this->failureRateThreshold;
    }

    /**
     * @param int $failureRateThreshold
     */
    public function setFailureRateThreshold(int $failureRateThreshold): void
    {
        $this->failureRateThreshold = $failureRateThreshold;
    }

    /**
     * @return int
     */
    public function getMinimumRequests(): int
    {
        return $this->minimumRequests;
    }

    /**
     * @param int $minimumRequests
     */
    public function setMinimumRequests(int $minimumRequests): void
    {
        $this->minimumRequests = $minimumRequests;
    }

    /**
     * @return int
     */
    public function getIntervalToHalfOpen(): int
    {
        return $this->intervalToHalfOpen;
    }

    /**
     * @param int $intervalToHalfOpen
     */
    public function setIntervalToHalfOpen(int $intervalToHalfOpen): void
    {
        $this->intervalToHalfOpen = $intervalToHalfOpen;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }
}