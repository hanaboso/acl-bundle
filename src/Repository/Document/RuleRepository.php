<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Repository\Document;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Class RuleRepository
 *
 * @package Hanaboso\AclBundle\Repository\Document
 *
 * @phpstan-extends DocumentRepository<\Hanaboso\AclBundle\Document\Rule>
 */
class RuleRepository extends DocumentRepository
{

}
