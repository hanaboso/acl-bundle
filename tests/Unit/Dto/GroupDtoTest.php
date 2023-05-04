<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Dto;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\UserBundle\Document\User;

/**
 * Class GroupDtoTest
 *
 * @package AclBundleTests\Unit\Dto
 *
 * @covers  \Hanaboso\AclBundle\Dto\GroupDto
 */
final class GroupDtoTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testDto(): void
    {
        $g = new Group(NULL);
        $d = new GroupDto($g, 'nae');
        $u = new User();

        $d->addUser($u);
        self::assertEquals([$u], $d->getUsers());

        $d->addRule(
            Rule::class,
            [
                [
                    RuleInterface::ACTION_MASK   => 5,
                    RuleInterface::ID   => '1',
                    RuleInterface::PROPERTY_MASK => 1,
                    RuleInterface::RESOURCE      => 'ress',
                ],
            ],
        );

        self::assertNotEmpty($d->getRules());

        self::assertEquals('nae', $d->getName());
        $d->setName('namae');
        self::assertEquals('namae', $d->getName());

        self::assertEquals($g, $d->getGroup());

        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::MISSING_DATA);
        $d->addRule(Rule::class, [[]]);
    }

}
