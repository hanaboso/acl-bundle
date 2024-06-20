<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Manager;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class AccessManagerTest
 *
 * @package AclBundleTests\Unit\Manager
 */
#[CoversClass(AccessManager::class)]
final class AccessManagerTest extends KernelTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     * @throws Exception
     */
    public function testAddGroupErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(),
            $this->mockResProvider(TRUE),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $a->addGroup('a');
    }

    /**
     * @throws Exception
     */
    public function testUpdateGroupErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $dto = new GroupDto(new Group(NULL), 'nae');

        $a->updateGroup($dto);
    }

    /**
     * @throws Exception
     */
    public function testRemoveGroupErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $a->removeGroup(new Group(NULL));
    }

    /**
     * @throws Exception
     */
    public function testCreateGroupErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(TRUE),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $a->createGroup(new UserEvent(new User()));
    }

    /**
     * @throws Exception
     */
    public function testCreateGroup(): void
    {
        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(),
            $this->mockResProvider(),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $a->createGroup(new UserEvent((new User())->setEmail('eml')));

        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testHasRightErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(TRUE),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $this->invokeMethod($a, 'hasRightForUser', [new User(), 1]);
    }

    /**
     * @throws Exception
     */
    public function testGetObjectByIdErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(TRUE),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $this->invokeMethod($a, 'getObjectById', [new Rule(), new User(), '', '']);
    }

    /**
     * @throws Exception
     */
    public function testSelectRuleErr(): void
    {
        self::expectException(AclException::class);

        $a = new AccessManager(
            $this->mockDml(),
            self::getContainer()->get('hbpf.factory.rule'),
            self::getContainer()->get('hbpf.factory.mask'),
            $this->mockAcl(TRUE),
            $this->mockResProvider(TRUE),
            ResourceEnum::class,
            ActionEnum::class,
        );

        $ii = 1;
        $this->invokeMethod($a, 'selectRule', [new User(), '', '', &$ii]);
    }

    /**
     * @throws Exception
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertCount(1, AccessManager::getSubscribedEvents());
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
                    throw new ResourceProviderException();
                },
            );
        } else {
            $r->method('getResource')->willReturn(Group::class);
        }

        return $r;
    }

    /**
     * @return DatabaseManagerLocator
     */
    private function mockDml(): DatabaseManagerLocator
    {
        $dm = self::createMock(DocumentManager::class);

        $dml = self::createMock(DatabaseManagerLocator::class);
        $dml->method('get')->willReturn($dm);

        return $dml;
    }

    /**
     * @param bool $throw
     *
     * @return AclProvider
     */
    private function mockAcl(bool $throw = FALSE): AclProvider
    {
        $a = self::createMock(AclProvider::class);
        if ($throw) {
            $a->method('invalid')->willReturnCallback(
                static function (): void {
                    throw new LogicException();
                },
            );
            $a->method('getRules')->willReturnCallback(
                static function (): void {
                    throw new LogicException();
                },
            );
        }

        return $a;
    }

}
