<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;

/**
 * Class RuleTest
 *
 * @package AclBundleTests\Unit\Document
 *
 * @covers  \Hanaboso\AclBundle\Document\Rule
 * @covers  \Hanaboso\AclBundle\Document\DocumentAbstract
 */
final class RuleTest extends KernelTestCaseAbstract
{

    /**
     *
     */
    public function testDocument(): void
    {
        $doc = new Rule();
        $doc->setResource('res');
        self::assertEquals('res', $doc->getResource());

        $g = new Group(NULL);
        $doc->setGroup($g);
        self::assertEquals($g, $doc->getGroup());

        $doc->setActionMask(2);
        self::assertEquals(2, $doc->getActionMask());

        $doc->setPropertyMask(3);
        self::assertEquals(3, $doc->getPropertyMask());

        $arr = $doc->toArrayAcl();
        self::assertEquals(
            [
                'id'            => NULL,
                'property_mask' => 3,
                'action_mask'   => 2,
                'resource'      => 'res',
            ],
            $arr
        );

        $arr = [
            Rule::ID            => '1',
            Rule::PROPERTY_MASK => 1,
            Rule::ACTION_MASK   => 5,
            Rule::RESOURCE      => 'ress',
        ];

        $doc->fromArrayAcl($arr);
        self::assertEquals($arr, $doc->toArrayAcl());
    }

}
