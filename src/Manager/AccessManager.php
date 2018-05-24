<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Annotation\OwnerAnnotation;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\AclBundle\Provider\Impl\DatabaseProvider;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as DocumentGroupRepository;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as EntityGroupRepository;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccessManager
 *
 * @package Hanaboso\AclBundle\Manager
 */
class AccessManager implements EventSubscriberInterface
{

    /**
     * @var DocumentManager|EntityManager
     */
    private $dm;

    /**
     * @var RuleFactory
     */
    private $factory;

    /**
     * @var DatabaseProvider
     */
    private $dbProvider;

    /**
     * @var ResourceProvider
     */
    private $resProvider;

    /**
     * @var string
     */
    private $resEnum;

    /**
     * AccessManager constructor.
     *
     * @param DatabaseManagerLocator $userDml
     * @param RuleFactory            $factory
     * @param DatabaseProvider       $dbProvider
     * @param ResourceProvider       $resProvider
     * @param string                 $resEnum
     */
    function __construct(
        DatabaseManagerLocator $userDml,
        RuleFactory $factory,
        DatabaseProvider $dbProvider,
        ResourceProvider $resProvider,
        string $resEnum
    )
    {
        $this->dm          = $userDml->get();
        $this->factory     = $factory;
        $this->dbProvider  = $dbProvider;
        $this->resProvider = $resProvider;
        $this->resEnum     = $resEnum;
    }

    /**
     * @param string $name
     *
     * @return GroupInterface
     * @throws UserException
     */
    public function addGroup(string $name): GroupInterface
    {
        $class = $this->resProvider->getResource(ResourceEnum::GROUP);
        /** @var GroupInterface $group */
        $group = new $class(NULL);
        $group->setName($name);
        $this->dm->persist($group);
        $this->dm->flush($group);

        return $group;
    }

    /**
     * @param GroupDto $data
     *
     * @return GroupInterface
     */
    public function updateGroup(GroupDto $data): GroupInterface
    {
        $group = $data->getGroup();

        if ($group->getRules()) {
            foreach ($group->getRules() as $rule) {
                $this->dm->remove($rule);
            }
        }

        if ($data->getName()) {
            $group->setName($data->getName());
        }

        $group->setUsers($data->getUsers());
        $group->setRules($data->getRules());

        if ($data->getRules()) {
            foreach ($data->getRules() as $rule) {
                $this->dm->persist($rule);
            }
        }

        $this->dm->flush();

        return $group;
    }

    /**
     * @param GroupInterface $group
     */
    public function removeGroup(GroupInterface $group): void
    {
        foreach ($group->getRules() as $rule) {
            $this->dm->remove($rule);
        }
        /** @var GroupInterface $child */
        foreach ($group->getChildren() as $child) {
            $child->removeParent($group);
        }

        $this->dm->remove($group);
        $this->dm->flush();
    }

    /**
     * @param UserEvent $event
     *
     * @throws AclException
     * @throws UserException
     */
    public function createGroup(UserEvent $event): void
    {
        $user  = $event->getUser();
        $class = $this->resProvider->getResource(ResourceEnum::GROUP);
        /** @var GroupInterface $group */
        $group = new $class($user);
        $group
            ->setName($user->getEmail())
            ->addUser($user);

        $this->factory->getDefaultRules($group);
        $this->dm->flush();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserEvent::USER_ACTIVATE => 'createGroup',
        ];
    }

    /**
     * Possible ways of use:
     * $act -> desired action (from ActionEnum)
     * $res -> desired resource (from ResourceEnum)
     * $user -> current user asking for permission
     *
     * $object:
     *  - NULL -> check if $user has permission for Write or GroupPermission for Read & Delete
     *      isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser);
     *      returns TRUE if allowed or throws an exception
     *
     *  - string -> id of desired entity
     *      isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, '1258');
     *      returns desired entity if found and user has permission for asked action or throws an exception
     *
     *  - object -> check permission for given entity
     *      isAllowed(ActionEnum::READ, ResourceEnum::Node, $loggedUser, $something);
     *      returns back given object or throws an exception
     *
     *  - other formats like array or int will only throws an exception
     *
     * @param string        $act
     * @param string        $res
     * @param UserInterface $user
     * @param mixed|null    $object
     *
     * @return mixed
     * @throws AclException
     */
    public function isAllowed(string $act, string $res, UserInterface $user, $object = NULL)
    {
        $this->checkParams($act, $res);
        $userLvl = 999;
        $rule    = $this->selectRule($user, $act, $res, $userLvl);

        if (is_string($object)) {

            return $this->checkObjectPermission($rule, $this->getObjectById($rule, $user, $res, $object),
                $user, $userLvl, $res, TRUE);

        } else if (is_object($object)) {

            return $this->checkObjectPermission($rule, $object, $user, $userLvl, $res);

        } else if (is_null($object)) {

            if ($act != ActionEnum::WRITE && $rule->getPropertyMask() !== 2) {
                $this->throwPermissionException('For given action no group permission or non at all for write action.');
            }

            return TRUE;

        } else {
            $this->throwPermissionException('Given object should be entity or it\'s id or null in case of write permission.');
        }

        return NULL;
    }

    /**
     * @param RuleInterface $rule
     * @param mixed         $object
     * @param UserInterface $user
     * @param int           $userLvl
     * @param string        $res
     * @param bool          $checkedGroup
     *
     * @return mixed
     * @throws AclException
     */
    private function checkObjectPermission(
        RuleInterface $rule,
        $object,
        UserInterface $user,
        int $userLvl,
        string $res,
        bool $checkedGroup = FALSE
    )
    {
        if (!$checkedGroup && $rule->getPropertyMask() === 1
            && property_exists($object, 'owner')
        ) {
            if ($user->getId() !== (is_string($object->getOwner())
                    ? $object->getOwner() : $object->getOwner()->getId())
            ) {
                $this->throwPermissionException('User has no permission from given object and action.');
            }
        }

        if ($res === ResourceEnum::GROUP) {
            return $this->hasRightForGroup($object, $userLvl);
        } else if ($res === ResourceEnum::USER) {
            return $this->hasRightForUser($object, $userLvl);
        }

        return $object;
    }

    /**
     * @param UserInterface $user
     * @param string        $act
     * @param string        $res
     * @param int           $userLvl
     *
     * @return RuleInterface
     * @throws AclException
     * @throws UserException
     */
    private function selectRule(UserInterface $user, string $act, string $res, int &$userLvl): RuleInterface
    {
        $rules     = $this->dbProvider->getRules($user);
        $bit       = MaskFactory::getActionByte($act);
        $rule      = NULL;
        $groupRule = FALSE;

        foreach ($rules as $val) {
            if ($this->hasRight($val, $res, $bit)) {

                if ($val->getPropertyMask() === 2) {
                    $groupRule = TRUE;
                    $this->checkGroupLvl($rule, $val, $userLvl);
                } else if (!$groupRule) {
                    $this->checkGroupLvl($rule, $val, $userLvl);
                }

            }
        }

        if (!$rule) {
            $this->throwPermissionException('User has no permission on [%s] resource for desired action.', $res);
        }

        return $rule;
    }

    /**
     * @param RuleInterface|null $old
     * @param RuleInterface      $new
     * @param int                $userLvl
     */
    private function checkGroupLvl(?RuleInterface &$old, RuleInterface $new, int &$userLvl): void
    {
        if (is_null($old) || ($old->getGroup()->getLevel() > $new->getGroup()->getLevel())) {
            $old     = $new;
            $userLvl = $new->getGroup()->getLevel();
        }
    }

    /**
     * @param RuleInterface $rule
     * @param UserInterface $user
     * @param string        $res
     * @param string        $id
     *
     * @return mixed
     * @throws AclException
     * @throws UserException
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function getObjectById(RuleInterface $rule, UserInterface $user, string $res, string $id)
    {
        $params = ['id' => $id];

        $class = $this->resProvider->getResource($res);
        if ((new ReflectionClass($class))->hasProperty('owner') && $rule->getPropertyMask() === 1) {

            $reader          = new AnnotationReader();
            $params['owner'] = $reader->getPropertyAnnotation(
                new ReflectionProperty($class, 'owner'), OwnerAnnotation::class)
                ? $user : $user->getId();
        }

        $res = $this->dm->getRepository($class)->findOneBy($params);

        if (!$res) {
            $this->throwPermissionException(sprintf(
                'User has no permission on entity with [%s] id or it doesn\'t exist.',
                $id
            ));
        }

        return $res;
    }

    /**
     * @param string      $message
     * @param null|string $id
     *
     * @throws AclException
     */
    private function throwPermissionException(string $message, ?string $id = NULL): void
    {
        $message = is_null($id) ? $message : sprintf($message, $id);

        throw new AclException(
            $message,
            AclException::PERMISSION
        );
    }

    /**
     * @param string $act
     * @param string $res
     *
     * @throws AclException
     */
    private function checkParams(string $act, string $res): void
    {
        if (!ActionEnum::isValid($act) || !($this->resEnum)::isValid($res)) {
            throw new AclException(
                'Invalid resource or action type.',
                AclException::INVALID_RESOURCE
            );
        }
    }

    /**
     * @param RuleInterface $rule
     * @param string        $res
     * @param int           $byte
     *
     * @return bool
     */
    private function hasRight(RuleInterface $rule, string $res, int $byte): bool
    {
        return $rule->getResource() === $res && $rule->getActionMask() >> $byte & 1;
    }

    /**
     * @param UserInterface $user
     * @param int           $userLvl
     *
     * @return UserInterface|null
     * @throws AclException
     * @throws UserException
     */
    private function hasRightForUser(UserInterface $user, int $userLvl): ?UserInterface
    {
        /** @var EntityGroupRepository|DocumentGroupRepository $repo */
        $repo   = $this->dm->getRepository($this->resProvider->getResource(ResourceEnum::GROUP));
        $groups = $repo->getUserGroups($user);

        foreach ($groups as $group) {
            if ($group->getLevel() < $userLvl) {
                $this->throwPermissionException('User has lower permission than [%s] user.', $group->getId());
            }
        }

        return $user;
    }

    /**
     * @param GroupInterface $group
     * @param int            $userLvl
     *
     * @return GroupInterface|null
     * @throws AclException
     */
    private function hasRightForGroup(GroupInterface $group, int $userLvl): ?GroupInterface
    {
        if ($group->getLevel() < $userLvl) {
            $this->throwPermissionException('User has lower permission than [%s] group.', $group->getId());
        }

        return $group;
    }

}