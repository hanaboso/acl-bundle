<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class GroupRepository
 *
 * @package         Hanaboso\AclBundle\Repository\Entity
 *
 * @phpstan-extends EntityRepository<Group>
 */
class GroupRepository extends EntityRepository
{

    /**
     * @param UserInterface $user
     *
     * @return GroupInterface[]
     */
    public function getUserGroups(UserInterface $user): array
    {
        /** @var GroupInterface[] $groups */
        $groups = $this->createQueryBuilder('g')
            ->join('g.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $ids = [];

        while ($group = current($groups)) {
            $ids[] = $group->getId();

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
     * @throws NoResultException
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
