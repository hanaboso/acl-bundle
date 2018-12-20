<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

    /**
     * @param string $name
     *
     * @return bool
     * @throws NonUniqueResultException
     */
    public function exists(string $name): bool
    {
        $c = (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id) as count')
            ->where('g.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getSingleScalarResult();

        return $c > 0;
    }

}