<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Document;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Hanaboso\AclBundle\Document\Rule;

/**
 * Class RuleRepository
 *
 * @package Hanaboso\AclBundle\Repository\Document
 *
 * @phpstan-extends DocumentRepository<Rule>
 */
class RuleRepository extends DocumentRepository
{

}
