<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Entity;

use Doctrine\ORM\EntityRepository;
use Hanaboso\AclBundle\Entity\Rule;

/**
 * Class RuleRepository
 *
 * @package Hanaboso\AclBundle\Repository\Entity
 *
 * @phpstan-extends EntityRepository<Rule>
 */
class RuleRepository extends EntityRepository
{

}
