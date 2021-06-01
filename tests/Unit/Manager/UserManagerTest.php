<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Manager;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\AclBundle\Manager\UserManager;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\UserBundle\Model\User\Event\DeleteBeforeUserEvent;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;

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
            UserManager::getSubscribedEvents(),
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
        return self::createMock(AccessManager::class);
    }

    /**
     * @return UserEvent
     */
    private function mockUserEvent(): UserEvent
    {
        return self::createMock(UserEvent::class);
    }

}
