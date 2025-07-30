<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Enum\ResourceEnum;
use Hanaboso\UserBundle\Model\User\Event\DeleteBeforeUserEvent;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserManager
 *
 * @package Hanaboso\AclBundle\Manager
 */
final class UserManager implements EventSubscriberInterface
{

    /**
     * UserManager constructor.
     *
     * @param AccessManager $accessManager
     */
    public function __construct(private readonly AccessManager $accessManager)
    {
    }

    /**
     * @param UserEvent $userEvent
     *
     * @throws AclException
     */
    public function checkPermission(UserEvent $userEvent): void
    {
        /** @var User|DmUser $user */
        $user = $userEvent->getLoggedUser();
        $this->accessManager->isAllowed(
            ActionEnum::DELETE,
            ResourceEnum::USER,
            $user,
            $userEvent->getUser(),
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DeleteBeforeUserEvent::NAME => 'checkPermission',
        ];
    }

}
