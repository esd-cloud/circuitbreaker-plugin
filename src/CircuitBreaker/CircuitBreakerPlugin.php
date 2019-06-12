<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/12
 * Time: 11:08
 */

namespace ESD\Plugins\CircuitBreaker;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Psr\Cloud\CircuitBreaker;

class CircuitBreakerPlugin extends AbstractPlugin
{

    /**
     * @var CircuitBreakerConfig|null
     */
    private $circuitBreakerConfig;

    /**
     * CircuitBreakerPlugin constructor.
     * @param CircuitBreakerConfig|null $circuitBreakerConfig
     * @throws \ReflectionException
     */
    public function __construct(?CircuitBreakerConfig $circuitBreakerConfig = null)
    {
        parent::__construct();
        if ($circuitBreakerConfig == null) {
            $circuitBreakerConfig = new CircuitBreakerConfig();
        }
        $this->circuitBreakerConfig = $circuitBreakerConfig;
        $this->atAfter(RedisPlugin::class);
    }

    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $this->pluginInterfaceManager->addPlug(new RedisPlugin());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "CircuitBreakerPlugin";
    }

    /**
     * 初始化
     * @param Context $context
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $ganesha = GaneshaCircuitBreaker::build([
            'timeWindow' => $this->circuitBreakerConfig->getTimeWindow(),
            'failureRateThreshold' => $this->circuitBreakerConfig->getFailureRateThreshold(),
            'minimumRequests' => $this->circuitBreakerConfig->getMinimumRequests(),
            'intervalToHalfOpen' => $this->circuitBreakerConfig->getIntervalToHalfOpen(),
            'db' => $this->circuitBreakerConfig->getRedisDb(),
            'adapter' => new RedisAdapter(),
        ]);
        $ganesha->setEnable($this->circuitBreakerConfig->isEnable());
        $this->setToDIContainer(CircuitBreaker::class, $ganesha);
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}