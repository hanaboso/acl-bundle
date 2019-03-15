<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\UserBundle\Enum\ResourceEnum;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use ReflectionException;
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
     *
     * @throws AclException
     * @throws AnnotationException
     * @throws MongoDBException
     * @throws UserException
     * @throws ReflectionException
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
