<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Manager;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\AclBundle\Manager\UserManager;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\UserBundle\Model\User\Event\DeleteBeforeUserEvent;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UserManagerTest
 *
 * @package AclBundleTests\Unit\Manager
 *
 * @covers  \Hanaboso\AclBundle\Manager\UserManager
 */
final class UserManagerTest extends KernelTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     * @covers \Hanaboso\AclBundle\Manager\UserManager::getSubscribedEvents
     */
    public function testArray(): void
    {
        self::assertEquals(
            [DeleteBeforeUserEvent::NAME => 'checkPermission'],
            UserManager::getSubscribedEvents()
        );
    }

    /**
     * @covers \Hanaboso\AclBundle\Manager\UserManager::checkPermission
     *
     * @throws Exception
     */
    public function testCheckPermission(): void
    {
        $u = new UserManager($this->mockAccessManager());
        $u->checkPermission($this->mockUserEvent());

        self::assertFake();
    }

    /**
     * @return AccessManager
     */
    private function mockAccessManager(): AccessManager
    {
        /** @var AccessManager|MockObject $a */
        $a = self::createMock(AccessManager::class);

        return $a;
    }

    /**
     * @return UserEvent
     */
    private function mockUserEvent(): UserEvent
    {
        /** @var UserEvent|MockObject $e */
        $e = self::createMock(UserEvent::class);

        return $e;
    }

}
