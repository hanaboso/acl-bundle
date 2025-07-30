<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Manager;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\AclBundle\Manager\UserManager;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Model\User\Event\DeleteBeforeUserEvent;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class UserManagerTest
 *
 * @package AclBundleTests\Unit\Manager
 */
#[CoversClass(UserManager::class)]
final class UserManagerTest extends KernelTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     * @return void
     */
    public function testArray(): void
    {
        self::assertEquals(
            [DeleteBeforeUserEvent::NAME => 'checkPermission'],
            UserManager::getSubscribedEvents(),
        );
    }

    /**
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
        $user  = new User();
        $event = self::createMock(UserEvent::class);
        $event->method('getLoggedUser')->willReturn($user);
        $event->method('getUser')->willReturn($user);

        return $event;
    }

}
