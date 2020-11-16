<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Cache;

/**
 * Interface ProviderCacheInterface
 *
 * @package Hanaboso\AclBundle\Cache
 */
interface ProviderCacheInterface
{

    /**
     * @param string $key
     *
     * @return mixed[]|null
     */
    public function get(string $key): ?array;

    /**
     * @param string  $key
     * @param int     $ttl
     * @param mixed[] $data
     */
    public function set(string $key, int $ttl, array $data): void;

    /**
     * @param string $key
     */
    public function delete(string $key): void;

}
