<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Query;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class GroupRepository
 *
 * @package Hanaboso\AclBundle\Repository\Document
 */
class GroupRepository extends DocumentRepository
{

    /**
     * @param UserInterface $user
     *
     * @return Group[]
     * @throws MongoDBException
     */
    public function getUserGroups(UserInterface $user): array
    {
        /** @var Query $query */
        $query = $this->createQueryBuilder()
            ->field('users')
            ->includesReferenceTo($user)
            ->getQuery()
            ->execute();

        $groups = $query->toArray();
        $ids    = [];
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
     * @throws MongoDBException
     */
    public function getTmpUserGroups(UserInterface $user): array
    {
        /** @var Query $query */
        $query = $this->createQueryBuilder()
            ->field('tmpUsers')
            ->includesReferenceTo($user)
            ->getQuery()
            ->execute();

        return $query->toArray();
    }

}