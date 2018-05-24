<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Entity;

use Doctrine\ORM\EntityRepository;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class GroupRepository
 *
 * @package Hanaboso\AclBundle\Repository\Entity
 */
class GroupRepository extends EntityRepository
{

    /**
     * @param UserInterface $user
     *
     * @return Group[]
     */
    public function getUserGroups(UserInterface $user): array
    {
        $groups = $this->createQueryBuilder('g')
            ->join('g.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $ids = [];
        /** @var GroupInterface $group */
        while ($group = current($groups)) {
            $ids[] = $group->getId();
            /** @var GroupInterface $parent */
            foreach ($group->getParents() as $parent) {
                if (!in_array($parent->getId(), $ids)) {
                    $groups[] = $parent;
                    $ids[]    = $parent->getId();
                }
            }

            next($groups);
        }

        return $groups;
    }

    /**
     * @param UserInterface $user
     *
     * @return Group[]
     */
    public function getTmpUserGroups(UserInterface $user): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.tmpUsers', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

}