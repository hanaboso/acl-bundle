<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Cache;

use Hanaboso\Utils\String\DsnParser;
use Hanaboso\Utils\String\Json;
use Predis\Client;

/**
 * Class RedisCache
 *
 * @package Hanaboso\AclBundle\Cache
 */
final class RedisCache implements ProviderCacheInterface
{

    /**
     * RedisCache constructor.
     *
     * @param string $redisDsn
     */
    public function __construct(private readonly string $redisDsn)
    {
    }

    /**
     * @param string $key
     *
     * @return mixed[]|null
     */
    public function get(string $key): ?array
    {
        $redis = $this->getClient();
        if (!$redis->exists($key)) {
            return NULL;
        }

        $json = $redis->get($key);

        return Json::decode($json ?? '{}');
    }

    /**
     * @param string  $key
     * @param int     $ttl
     * @param mixed[] $data
     */
    public function set(string $key, int $ttl, array $data): void
    {
        $this->getClient()->setex($key, $ttl, Json::encode($data));
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->getClient()->del([$key]);
    }

    /**
     * ---------------------------------- HELPERS ------------------------------
     */

    /**
     * @return Client<mixed>
     */
    private function getClient(): Client
    {
        $config = DsnParser::parseRedisDsn($this->redisDsn);

        $redis = new Client(
            [
                'host' => $config[DsnParser::HOST],
                'port' => $config[DsnParser::PORT] ?? 6_379,
            ],
        );
        $redis->connect();

        return $redis;
    }

}
