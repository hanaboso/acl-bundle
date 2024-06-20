<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Provider\Impl;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Hanaboso\AclBundle\Cache\NullCache;
use Hanaboso\AclBundle\Cache\RedisCache;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\AclBundle\Repository\Document\GroupRepository;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use Hanaboso\Utils\String\Json;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class AclProviderTest
 *
 * @package AclBundleTests\Unit\Provider\Impl
 */
#[CoversClass(AclProvider::class)]
final class AclProviderTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGetGroups(): void
    {
        $a = new AclProvider(
            $this->mockDml(),
            self::getContainer()->get('hbpf.user.provider.resource'),
            ResourceEnum::class,
            $this->mockRedis(
                static fn() => Json::decode(
                    '{"groups":[{"owner":null,"id":"id","name":"nae","level":1,"rules":[{"id":"rid","property_mask":1,"action_mask":1,"resource":"user"}]}],"links":{"rid":"id"}}',
                ),
            ),
        );

        $res = $a->getGroups($this->getUser());
        self::assertNotEmpty($res);
    }

    /**
     * @throws Exception
     */
    public function testException(): void
    {
        $a = self::getMockBuilder(AclProvider::class)->setConstructorArgs(
            [
                $this->mockDml(),
                self::getContainer()->get('hbpf.user.provider.resource'),
                ResourceEnum::class,
                new NullCache(),
            ],
        )->onlyMethods(['load'])->getMock();
        $a->method('load')->willReturnCallback(
            static function (): void {
                throw new ResourceProviderException();
            },
        );

        self::expectException(AclException::class);
        $a->getGroups($this->getUser());
    }

    /**
     * @throws Exception
     */
    public function testException2(): void
    {
        $a = new AclProvider(
            $this->mockDml(),
            self::getContainer()->get('hbpf.user.provider.resource'),
            ResourceEnum::class,
            $this->mockRedis(
                static function (): void {
                    throw new ResourceProviderException();
                },
            ),
        );

        self::expectException(AclException::class);
        $a->getGroups($this->getUser());
    }

    /**
     * @return DatabaseManagerLocator
     */
    private function mockDml(): DatabaseManagerLocator
    {
        $rep = self::createMock(GroupRepository::class);
        $dm  = self::createMock(DocumentManager::class);
        $dm->method('getRepository')->willReturn($rep);
        $dml = self::createMock(DatabaseManagerLocator::class);
        $dml->method('get')->willReturn($dm);

        return $dml;
    }

    /**
     * @param callable $return
     *
     * @return RedisCache
     */
    private function mockRedis(callable $return): RedisCache
    {
        $c = self::createMock(RedisCache::class);
        $c
            ->expects(self::exactly(1))
            ->method('get')
            ->willReturnCallback($return);

        return $c;
    }

    /**
     * @return User
     * @throws Exception
     */
    private function getUser(): User
    {
        $u = new User();
        $this->setProperty($u, 'id', 'id');

        return $u;
    }

}
