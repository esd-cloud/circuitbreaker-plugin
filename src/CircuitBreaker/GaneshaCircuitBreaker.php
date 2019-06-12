<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/12
 * Time: 12:43
 */

namespace ESD\Plugins\CircuitBreaker;


use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Configuration;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Psr\Cloud\CircuitBreaker;

class GaneshaCircuitBreaker extends Ganesha implements CircuitBreaker
{
    use GetLogger;
    private static $enable = true;

    /**
     * @param  array $params
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    public static function build(array $params)
    {
        $params['strategyClass'] = '\Ackintosh\Ganesha\Strategy\Rate';
        return self::perform($params);
    }

    /**
     * @param  array $params
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    public static function buildWithCountStrategy(array $params)
    {
        $params['strategyClass'] = '\Ackintosh\Ganesha\Strategy\Count';
        return self::perform($params);
    }

    /**
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    private static function perform($params)
    {
        call_user_func([$params['strategyClass'], 'validate'], $params);

        $configuration = new Configuration($params);
        $ganesha = new GaneshaCircuitBreaker(
            call_user_func(
                [$configuration['strategyClass'], 'create'],
                $configuration
            )
        );

        return $ganesha;
    }


    public function setEnable($enable)
    {
        self::$enable = $enable;
        if ($enable) {
            self::enable();
        } else {
            self::disable();
        }
    }

    public function isEnable()
    {
        return self::$enable;
    }

    public function isAvailable($service)
    {
        $result = parent::isAvailable($service);
        if ($result) {
            $this->debug("Service $service availability");
        } else {
            $this->debug("Service $service unavailability");
        }
        return $result;
    }

    public function failure($service)
    {
        parent::failure($service);
        $this->debug("Service $service tag failure");
    }

    public function success($service)
    {
        parent::success($service);
        $this->debug("Service $service tag success");
    }
}