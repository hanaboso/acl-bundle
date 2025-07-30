<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Dto;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\Rule as EntityRule;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\UserBundle\Document\User;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class GroupDtoTest
 *
 * @package AclBundleTests\Unit\Dto
 */
#[CoversClass(GroupDto::class)]
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
                    EntityRule::ACTION_MASK   => 5,
                    EntityRule::ID   => '1',
                    EntityRule::PROPERTY_MASK => 1,
                    EntityRule::RESOURCE      => 'ress',
                ],
            ],
        );

        self::assertNotEmpty($d->getRules());

        self::assertSame('nae', $d->getName());
        $d->setName('namae');
        self::assertSame('namae', $d->getName());

        self::assertEquals($g, $d->getGroup());

        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::MISSING_DATA);
        $d->addRule(Rule::class, [[]]);
    }

}
