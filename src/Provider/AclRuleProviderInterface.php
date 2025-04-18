<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider;

use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Interface AclRuleProviderInterface
 *
 * @package Hanaboso\AclBundle\Provider
 */
interface AclRuleProviderInterface
{

    public const string PREFIX = 'acl_user';

    /**
     * @param UserInterface $user
     *
     * @return GroupInterface[]
     */
    public function getGroups(UserInterface $user): array;

    /**
     * @param UserInterface $user
     * @param int           $userLvl
     *
     * @return RuleInterface[]
     */
    public function getRules(UserInterface $user, int &$userLvl): array;

    /**
     * @param string[] $users
     */
    public function invalid(array $users): void;

}
