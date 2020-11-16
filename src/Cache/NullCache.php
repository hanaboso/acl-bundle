<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Cache;

/**
 * Class NullCache
 *
 * @package Hanaboso\AclBundle\Cache
 */
final class NullCache implements ProviderCacheInterface
{

    /**
     * @param string $key
     *
     * @return mixed[]|null
     */
    public function get(string $key): ?array
    {
        $key;

        return NULL;
    }

    /**
     * @param string  $key
     * @param int     $ttl
     * @param mixed[] $data
     */
    public function set(string $key, int $ttl, array $data): void
    {
        $key;
        $ttl;
        $data;
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        $key;
    }

}
