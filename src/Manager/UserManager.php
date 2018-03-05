<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\UserBundle\Enum\ResourceEnum;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserManager
 *
 * @package Hanaboso\AclBundle\Manager
 */
class UserManager implements EventSubscriberInterface
{

    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * UserManager constructor.
     *
     * @param AccessManager $accessManager
     */
    public function __construct(AccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserEvent::USER_DELETE_BEFORE => 'checkPermission',
        ];
    }

    /**
     * @param UserEvent $userEvent
     */
    public function checkPermission(UserEvent $userEvent): void
    {
        $this->accessManager->isAllowed(
            ActionEnum::DELETE,
            ResourceEnum::USER,
            $userEvent->getLoggedUser(),
            $userEvent->getUser()
        );
    }

}