<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Provider\Impl;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\AclBundle\Repository\Document\GroupRepository;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use Predis\Client;

/**
 * Class AclProviderTest
 *
 * @package AclBundleTests\Unit\Provider\Impl
 *
 * @covers  \Hanaboso\AclBundle\Provider\Impl\AclProvider
 */
final class AclProviderTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGetGroups(): void
    {
        $a = self::getMockBuilder(AclProvider::class)->setConstructorArgs(
            [
                $this->mockDml(),
                self::$container->get('hbpf.user.provider.resource'),
                ResourceEnum::class,
                'true',
                '',
            ]
        )->onlyMethods(['getClient'])->getMock();
        $a->method('getClient')->willReturn($this->mockRedis());

        $u = new User();
        $this->setProperty($u, 'id', 'id');
        $res = $a->getGroups($u);

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
                self::$container->get('hbpf.user.provider.resource'),
                ResourceEnum::class,
                'true',
                '',
            ]
        )->onlyMethods(['load'])->getMock();
        $a->method('load')->willReturnCallback(
            static function (): void {
                throw new ResourceProviderException();
            }
        );

        self::expectException(AclException::class);
        $a->getGroups(new User());
    }

    /**
     * @throws Exception
     */
    public function testException2(): void
    {
        $a = self::getMockBuilder(AclProvider::class)->setConstructorArgs(
            [
                $this->mockDml(),
                self::$container->get('hbpf.user.provider.resource'),
                ResourceEnum::class,
                'true',
                '',
            ]
        )->onlyMethods(['getClient'])->getMock();
        $a->method('getClient')->willReturnCallback(
            static function (): void {
                throw new ResourceProviderException();
            }
        );

        self::expectException(AclException::class);
        $a->getGroups(new User());
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
     * @return Client<mixed>
     */
    private function mockRedis(): Client
    {
        $c = self::createMock(Client::class);
        $c
            ->expects(self::exactly(2))
            ->method('__call')
            ->willReturnOnConsecutiveCalls(
                TRUE,
                '{"groups":[{"owner":null,"id":"id","name":"nae","level":1,"rules":[{"id":"rid","property_mask":1,"action_mask":1,"resource":"user"}]}],"links":{"rid":"id"}}'
            );

        return $c;
    }

}
