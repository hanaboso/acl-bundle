<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Document;

use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class GroupRepository
 *
 * @package         Hanaboso\AclBundle\Repository\Document
 *
 * @phpstan-extends DocumentRepository<Group>
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
        /** @var Iterator<Group> $query */
        $query = $this->createQueryBuilder()
            ->field('users')
            ->includesReferenceTo($user)
            ->getQuery()
            ->execute();

        /** @var Group[] $groups */
        $groups = $query->toArray();
        $ids    = [];
        reset($groups);

        while ($group = current($groups)) {
            $ids[] = $group->getId();
            /** @var Group $parent */
            foreach ($group->getParents() as $parent) {
                // phpcs:disable
                if (!in_array($parent->getId(), $ids, FALSE)) {
                    // phpcs:enable
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
        /** @var Iterator<Group> $query */
        $query = $this->createQueryBuilder()
            ->field('tmpUsers')
            ->includesReferenceTo($user)
            ->getQuery()
            ->execute();

        return $query->toArray();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function exists(string $name): bool
    {
        $g = $this->createQueryBuilder()
            ->field('name')->equals($name)
            ->getQuery()
            ->getSingleResult();

        return !is_null($g);
    }

}
