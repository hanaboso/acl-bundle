<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Hanaboso\AclBundle\Attribute\OwnerAttribute;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as DocumentGroupRepository;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as EntityGroupRepository;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Model\User\Event\ActivateUserEvent;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccessManager
 *
 * @package Hanaboso\AclBundle\Manager
 */
final class AccessManager implements EventSubscriberInterface
{

    /**
     * @var DocumentManager|EntityManager
     */
    private DocumentManager|EntityManager $dm;

    /**
     * AccessManager constructor.
     *
     * @param DatabaseManagerLocator $userDml
     * @param RuleFactory            $factory
     * @param MaskFactory            $maskFactory
     * @param AclProvider            $aclProvider
     * @param ResourceProvider       $resProvider
     * @param string                 $resEnum
     * @param string                 $actionEnum
     */
    function __construct(
        DatabaseManagerLocator $userDml,
        private readonly RuleFactory $factory,
        private readonly MaskFactory $maskFactory,
        private readonly AclProvider $aclProvider,
        private readonly ResourceProvider $resProvider,
        private readonly string $resEnum,
        private readonly string $actionEnum,
    )
    {
        $this->dm = $userDml->get();
    }

    /**
     * @param string $name
     *
     * @return Group|DmGroup
     * @throws AclException
     * @throws MongoDBException
     */
    public function addGroup(string $name): Group|DmGroup
    {
        try {
            $class = $this->resProvider->getResource(ResourceEnum::GROUP);
            /** @var Group|DmGroup $group */
            $group = new $class(NULL);
            $group->setName($name);
            $this->dm->persist($group);
            $this->dm->flush();

            return $group;
        } catch (ORMException | ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param GroupDto $data
     *
     * @return Group|DmGroup
     * @throws AclException
     * @throws MongoDBException
     */
    public function updateGroup(GroupDto $data): Group|DmGroup
    {
        try {
            $group = $data->getGroup();

            foreach ($group->getRules() as $rule) {
                $this->dm->remove($rule);
            }

            if ($data->getName()) {
                $group->setName($data->getName());
            }

            $this->aclProvider->invalid(
                array_merge(
                    array_map(static fn(User|DmUser $user) => $user->getId(), $group->getUsers()->toArray()),
                    array_map(static fn(User|DmUser $user) => $user->getId(), $data->getUsers()),
                ),
            );

            $group->setUsers($data->getUsers());
            $group->setRules($data->getRules());

            if ($data->getRules()) {
                foreach ($data->getRules() as $rule) {
                    $this->dm->persist($rule);
                }
            }

            $this->dm->flush();

            return $group;
        } catch (ORMException | LogicException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Group|DmGroup $group
     *
     * @throws AclException
     * @throws MongoDBException
     */
    public function removeGroup(Group|DmGroup $group): void
    {
        try {
            $this->aclProvider->invalid(
                array_map(static fn(User|DmUser $user) => $user->getId(), $group->getUsers()->toArray()),
            );

            foreach ($group->getRules() as $rule) {
                $this->dm->remove($rule);
            }
            /** @var Group|DmGroup $child */
            foreach ($group->getChildren() as $child) {
                $child->removeParent($group);
            }

            $this->dm->remove($group);
            $this->dm->flush();
        } catch (LogicException | ORMException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param UserEvent $event
     *
     * @throws AclException
     * @throws MongoDBException
     */
    public function createGroup(UserEvent $event): void
    {
        try {
            /** @var User|DmUser $user */
            $user  = $event->getUser();
            $class = $this->resProvider->getResource(ResourceEnum::GROUP);
            /** @var Group|DmGroup $group */
            $group = new $class($user);
            $group
                ->setName($user->getEmail())
                ->addUser($user);

            $this->factory->getDefaultRules($group);
            $this->dm->flush();
        } catch (ResourceProviderException | ORMException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
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
     * @param string      $act
     * @param string      $res
     * @param User|DmUser $user
     * @param mixed|null  $object
     *
     * @return mixed
     * @throws AclException
     */
    public function isAllowed(string $act, string $res, User|DmUser $user, $object = NULL): mixed
    {
        $this->checkParams($act, $res);
        $userLvl = 999;
        $rule    = $this->selectRule($user, $act, $res, $userLvl);

        if (is_string($object)) {
            return $this->checkObjectPermission(
                $rule,
                $this->getObjectById($rule, $user, $res, $object),
                $user,
                $userLvl,
                $res,
                TRUE,
            );
        } else if (is_object($object)) {
            return $this->checkObjectPermission($rule, $object, $user, $userLvl, $res);
        } else if (is_null($object)) {

            if (!in_array($act, $this->actionEnum::getGlobalActions(), TRUE) && $rule->getPropertyMask() !== 2) {
                throw $this->getPermissionException(
                    'For given action no group permission or non at all for global actions.',
                );
            }

            return TRUE;
        } else {
            throw $this->getPermissionException(
                'Given object should be entity or it\'s id or null in case of write permission.',
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ActivateUserEvent::NAME => 'createGroup',
        ];
    }

    /**
     * @param Rule|DmRule $rule
     * @param mixed       $obj
     * @param User|DmUser $user
     * @param int         $userLvl
     * @param string      $res
     * @param bool        $checkedGroup
     *
     * @return mixed
     * @throws AclException
     */
    private function checkObjectPermission(
        Rule|DmRule $rule,
        mixed $obj,
        User|DmUser $user,
        int $userLvl,
        string $res,
        bool $checkedGroup = FALSE,
    ): mixed
    {
        if (!$checkedGroup && $rule->getPropertyMask() === 1 && method_exists($obj, 'getOwner')) {
            if ($user->getId() !== (is_string($obj->getOwner()) ? $obj->getOwner() : $obj->getOwner()->getId())) {
                throw $this->getPermissionException('User has no permission from given object and action.');
            }
        }

        if ($res === ResourceEnum::GROUP) {
            return $this->hasRightForGroup($obj, $userLvl);
        } else if ($res === ResourceEnum::USER) {
            return $this->hasRightForUser($obj, $userLvl);
        }

        return $obj;
    }

    /**
     * @param User|DmUser $user
     * @param string      $act
     * @param string      $res
     * @param int         $userLvl
     *
     * @return Rule|DmRule
     * @throws AclException
     */
    private function selectRule(User|DmUser $user, string $act, string $res, int &$userLvl): Rule|DmRule
    {
        try {
            $rules     = $this->aclProvider->getRules($user, $userLvl);
            $bit       = $this->actionEnum::getActionBit($act);
            $rule      = NULL;
            $groupRule = FALSE;

            foreach ($rules as $val) {
                if ($this->hasRight($val, $res, $bit)) {

                    if ($val->getPropertyMask() === 2) {
                        if ($groupRule) {
                            $this->checkGroupLvl($rule, $val);
                        } else {
                            $rule = $val;
                        }
                        $groupRule = TRUE;
                    } else if (!$groupRule) {
                        $this->checkGroupLvl($rule, $val);
                    }

                }
            }

            if (!$rule) {
                throw $this->getPermissionException(
                    'User has no permission on [%s] resource for desired action.',
                    $res,
                );
            }

            return $rule;
        } catch (ResourceProviderException | MongoDBException | LogicException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Rule|DmRule|null $old
     * @param Rule|DmRule      $new
     */
    private function checkGroupLvl(Rule|DmRule|null &$old, Rule|DmRule $new): void // @phpstan-ignore-line
    {
        if (is_null($old) || ($old->getGroup()->getLevel() > $new->getGroup()->getLevel())) {
            $old = $new;
        }
    }

    /**
     * @param Rule|DmRule $rule
     * @param User|DmUser $user
     * @param string      $res
     * @param string      $id
     *
     * @return mixed
     * @throws AclException
     */
    private function getObjectById(Rule|DmRule $rule, User|DmUser $user, string $res, string $id): mixed
    {
        try {
            $params = ['id' => $id];

            /** @phpstan-var class-string<object> $class */
            $class           = $this->resProvider->getResource($res);
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->hasProperty('owner') && $rule->getPropertyMask() === 1) {
                $owner           = $reflectionClass->getProperty('owner')->getAttributes(OwnerAttribute::class);
                $params['owner'] = $owner ? $user : $user->getId();
            }

            $res = $this->dm->getRepository($class)->findOneBy($params);

            if (!$res) {
                throw $this->getPermissionException(
                    sprintf(
                        'User has no permission on entity with [%s] id or it doesn\'t exist.',
                        $id,
                    ),
                );
            }

            return $res;
        } catch (ResourceProviderException | ReflectionException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string      $message
     * @param string|null $id
     *
     * @return AclException
     */
    private function getPermissionException(string $message, string|int|null $id = NULL): AclException
    {
        $message = is_null($id) ? $message : sprintf($message, $id);

        return new AclException($message, AclException::PERMISSION);
    }

    /**
     * @param string $act
     * @param string $res
     *
     * @throws AclException
     */
    private function checkParams(string $act, string $res): void
    {
        $this->actionEnum::isValid($act);
        $this->resEnum::isValid($res);

        if (!$this->maskFactory->isActionAllowed($act, $res)) {
            throw new AclException(
                sprintf('Action [%s] is not allowed for resource [%s].', $act, $res),
                AclException::INVALID_ACTION,
            );
        }
    }

    /**
     * @param Rule|DmRule $rule
     * @param string      $res
     * @param int         $byte
     *
     * @return bool
     */
    private function hasRight(Rule|DmRule $rule, string $res, int $byte): bool
    {
        return $rule->getResource() === $res && $rule->getActionMask() >> $byte & 1;
    }

    /**
     * @param User|DmUser $user
     * @param int         $userLvl
     *
     * @return User|DmUser
     * @throws AclException
     */
    private function hasRightForUser(User|DmUser $user, int $userLvl): User|DmUser
    {
        try {
            /** @phpstan-var class-string<Group|DmGroup> $groupClass */
            $groupClass = $this->resProvider->getResource(ResourceEnum::GROUP);
            /** @var EntityGroupRepository|DocumentGroupRepository $repo */
            $repo   = $this->dm->getRepository($groupClass);
            $groups = $repo->getUserGroups($user);

            foreach ($groups as $group) {
                if ($group->getLevel() < $userLvl) {
                    throw $this->getPermissionException('User has lower permission than [%s] user.', $group->getId());
                }
            }

            return $user;
        } catch (ResourceProviderException | MongoDBException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Group|DmGroup $group
     * @param int           $userLvl
     *
     * @return Group|DmGroup
     * @throws AclException
     */
    private function hasRightForGroup(Group|DmGroup $group, int $userLvl): Group|DmGroup
    {
        if ($group->getLevel() < $userLvl) {
            throw $this->getPermissionException('User has lower permission than [%s] group.', $group->getId());
        }

        return $group;
    }

}
