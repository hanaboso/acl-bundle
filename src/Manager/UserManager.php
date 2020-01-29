<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\UserBundle\Enum\ResourceEnum;
use Hanaboso\UserBundle\Model\User\Event\DeleteBeforeUserEvent;
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
     * @param UserEvent $userEvent
     *
     * @throws AclException
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

    /**
     * @return array<string, array<int|string, array<int|string, int|string>|int|string>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DeleteBeforeUserEvent::class => 'checkPermission',
        ];
    }

}
