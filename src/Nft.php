<?php
namespace dh2y\nft;


use dh2y\nft\Contracts\GatewayApplicationInterface;
use dh2y\nft\Exceptions\InvalidGatewayException;
use dh2y\nft\Gateways\Wenchang;
use dh2y\nft\Supports\Config;
use dh2y\nft\Supports\Str;

/**
 * @method static Wenchang wenchang(array $config) 文昌链
 */
class Nft
{

    /**
     * Config.
     * @var Config
     */
    protected $config = [];

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);;
    }


    /**
     * Magic static call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array  $params
     *
     * @throws InvalidGatewayException
     * @throws Exception
     */
    public static function __callStatic($method, $params): GatewayApplicationInterface
    {
        $app = new self(...$params);

        return $app->create($method);
    }

    /**
     * Create a instance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     *
     * @throws InvalidGatewayException
     */
    protected function create($method): GatewayApplicationInterface
    {
        $gateway = __NAMESPACE__.'\\Gateways\\'.Str::studly($method);

        if (class_exists($gateway)) {
            return self::make($gateway);
        }

        throw new InvalidGatewayException("Gateway [{$method}] Not Exists");
    }

    /**
     * Make a gateway.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     *
     * @throws InvalidGatewayException
     */
    protected function make($gateway): GatewayApplicationInterface
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new InvalidGatewayException("Gateway [{$gateway}] Must Be An Instance Of GatewayApplicationInterface");
    }
}