<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider;

use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;

/**
 * Interface AclRuleProviderInterface
 *
 * @package Hanaboso\AclBundle\Provider
 */
interface AclRuleProviderInterface
{

    public const string PREFIX = 'acl_user';

    /**
     * @param User|DmUser $user
     *
     * @return Group[]|DmGroup[]
     */
    public function getGroups(User|DmUser $user): array;

    /**
     * @param User|DmUser $user
     * @param int         $userLvl
     *
     * @return Rule[]|DmRule[]
     */
    public function getRules(User|DmUser $user, int &$userLvl): array;

    /**
     * @param string[]|int[] $users
     */
    public function invalid(array $users): void;

}
