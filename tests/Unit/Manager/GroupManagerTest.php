<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Manager;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Manager\GroupManager;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\AclBundle\Repository\Document\GroupRepository;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;

/**
 * Class GroupManagerTest
 *
 * @package AclBundleTests\Unit\Manager
 */
final class GroupManagerTest extends KernelTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     * @throws Exception
     */
    public function testAddUserException(): void
    {
        self::expectException(AclException::class);

        $g = new GroupManager(
            $this->mockDml(),
            $this->mockResProvider(TRUE),
            $this->mockAclProvider(),
        );

        $g->addUserIntoGroup(new User(), 'a');
    }

    /**
     * @throws Exception
     */
    public function testAddUserException2(): void
    {
        self::expectException(AclException::class);

        $g = new GroupManager(
            $this->mockDml(TRUE),
            $this->mockResProvider(),
            $this->mockAclProvider(),
        );

        $g->addUserIntoGroup(new User(), 'a');
    }

    /**
     * @throws Exception
     */
    public function testGetGroupsErr(): void
    {
        self::expectException(AclException::class);

        $g = new GroupManager(
            $this->mockDml(),
            $this->mockResProvider(TRUE),
            $this->mockAclProvider(),
        );

        $g->getUserGroups(new User());
    }

    /**
     * @throws Exception
     */
    public function testRemoveErr(): void
    {
        self::expectException(AclException::class);

        $g = new GroupManager(
            $this->mockDml(),
            $this->mockResProvider(TRUE),
            $this->mockAclProvider(),
        );

        $g->removeUserFromGroup(new User(), 'a');
    }

    /**
     * @throws Exception
     */
    public function testRemoveErr2(): void
    {
        self::expectException(AclException::class);

        $g = new GroupManager(
            $this->mockDml(TRUE),
            $this->mockResProvider(),
            $this->mockAclProvider(),
        );

        $g->removeUserFromGroup(new User(), 'a');
    }

    /**
     * @param bool $throw
     *
     * @return DatabaseManagerLocator
     */
    private function mockDml(bool $throw = FALSE): DatabaseManagerLocator
    {
        $repo = self::createMock(GroupRepository::class);
        if ($throw) {
            $repo->method('findOneBy')->willReturnCallback(static fn() => NULL);
        }

        $dm = self::createMock(DocumentManager::class);
        $dm->method('getRepository')->willReturn($repo);

        $d = self::createMock(DatabaseManagerLocator::class);
        $d->method('get')->willReturn($dm);

        return $d;
    }

    /**
     * @param bool $throw
     *
     * @return ResourceProvider
     */
    private function mockResProvider(bool $throw = FALSE): ResourceProvider
    {
        $r = self::createMock(ResourceProvider::class);
        if ($throw) {
            $r->method('getResource')->willReturnCallback(
                static function (): void {
                    throw new ResourceProviderException('');
                },
            );
        } else {
            $r->method('getResource')->willReturn(ResourceEnum::GROUP);
        }

        return $r;
    }

    /**
     * @return AclProvider
     */
    private function mockAclProvider(): AclProvider
    {
        return self::createMock(AclProvider::class);
    }

}
