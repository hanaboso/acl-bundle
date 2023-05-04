<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Entity;

use AclBundleTests\KernelTestCaseAbstract;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Entity\RuleInterface;

/**
 * Class RuleTest
 *
 * @package AclBundleTests\Unit\Entity
 *
 * @covers  \Hanaboso\AclBundle\Entity\Rule
 * @covers  \Hanaboso\AclBundle\Entity\EntityAbstract
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
        $this->setProperty($doc, 'id', '1');
        self::assertEquals('res', $doc->getResource());

        $g = new Group(NULL);
        $doc->setGroup($g);
        $this->setProperty($g, 'id', '1');
        self::assertEquals($g, $doc->getGroup());

        $doc->setActionMask(2);
        self::assertEquals(2, $doc->getActionMask());

        $doc->setPropertyMask(3);
        self::assertEquals(3, $doc->getPropertyMask());

        $arr = $doc->toArrayAcl();
        self::assertEquals(
            [
                'action_mask'   => 2,
                'id'            => '1',
                'property_mask' => 3,
                'resource'      => 'res',
            ],
            $arr,
        );

        $arr = [
            RuleInterface::ACTION_MASK   => 5,
            RuleInterface::ID            => '1',
            RuleInterface::PROPERTY_MASK => 1,
            RuleInterface::RESOURCE      => 'ress',
        ];

        $doc->fromArrayAcl($arr);
        self::assertEquals($arr, $doc->toArrayAcl());
    }

}
