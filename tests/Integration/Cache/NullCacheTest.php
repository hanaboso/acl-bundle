<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Cache;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Cache\NullCache;

/**
 * Class NullCacheTest
 *
 * @package AclBundleTests\Integration\Cache
 */
final class NullCacheTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testCache(): void
    {
        $cache = new NullCache();

        $cache->set('key', 1, []);
        $res = $cache->get('key');
        $cache->delete('key');
        self::assertEquals(NULL, $res);
    }

}
