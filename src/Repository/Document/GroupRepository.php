<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Query;
use Hanaboso\AclBundle\Document\Group;
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
     */
    public function getUserGroups(UserInterface $user): array
    {
        /** @var Query $query */
        $query = $this->createQueryBuilder()
            ->field('users')
            ->includesReferenceTo($user)
            ->getQuery()
            ->execute();

        return $query->toArray();
    }

}