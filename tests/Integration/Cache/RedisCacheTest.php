<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Cache;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Cache\RedisCache;

/**
 * Class RedisCacheTest
 *
 * @package AclBundleTests\Integration\Cache
 */
final class RedisCacheTest extends DatabaseTestCaseAbstract
{

    private const string KEY   = 'key';
    private const string KEY_2 = 'key2';

    /**
     * @throws Exception
     */
    public function testCache(): void
    {
        $ex    = [self::KEY => 'val'];
        $cache = new RedisCache('redis://redis/10');

        $cache->set(self::KEY, 100, $ex);
        $cache->set(self::KEY_2, 1, $ex);

        $res = $cache->get(self::KEY);
        self::assertEquals($ex, $res);

        $cache->delete(self::KEY);
        $res = $cache->get(self::KEY);
        self::assertEquals(NULL, $res);

        sleep(1);
        $res = $cache->get(self::KEY_2);
        self::assertEquals(NULL, $res);
    }

}
