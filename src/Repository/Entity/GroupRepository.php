<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Entity;

use Doctrine\ORM\EntityRepository;
use Hanaboso\AclBundle\Document\Group;
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
        return $this->createQueryBuilder('g')
            ->join('g.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
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