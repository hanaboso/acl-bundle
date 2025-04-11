<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Factory;

use AclBundleTests\KernelTestCaseAbstract;
use AclBundleTests\testApp\ExtActionEnum;
use Exception;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\MaskFactory;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class MaskFactoryTest
 *
 * @package AclBundleTests\Unit\Factory
 */
#[CoversClass(MaskFactory::class)]
final class MaskFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testMaskAction(): void
    {
        $factory = self::getContainer()->get('hbpf.factory.mask');
        //@codingStandardsIgnoreLine
        $data    = ['read'   => FALSE, 'write', 'delete' => 'true'];

        self::assertSame(6, $factory->maskAction($data, ResourceEnum::TOKEN));
    }

    /**
     * @throws Exception
     */
    public function testMaskProperty(): void
    {
        $data = [
            'group' => 1,
            'owner' => '1',
        ];

        self::assertSame(2, MaskFactory::maskProperty($data));
    }

    /**
     * @throws Exception
     */
    public function testAllowedList(): void
    {
        $factory  = self::getContainer()->get('hbpf.factory.mask');
        $baseList = [
            ActionEnum::READ,
            ActionEnum::WRITE,
            ActionEnum::DELETE,
            ExtActionEnum::TEST,
        ];

        self::assertEquals(
            [
                MaskFactory::DEFAULT_ACTIONS => $baseList,
                MaskFactory::RESOURCE_LIST   => [
                    ResourceEnum::TOKEN => [
                        ExtActionEnum::TEST2,
                    ],
                ],
            ],
            $factory->getAllowedList(FALSE),
        );

        self::assertEquals(
            [
                ResourceEnum::FILE     => $baseList,
                ResourceEnum::GROUP    => $baseList,
                ResourceEnum::RULE     => $baseList,
                ResourceEnum::TMP_USER => $baseList,
                ResourceEnum::TOKEN    => [
                    ActionEnum::READ,
                    ActionEnum::WRITE,
                    ActionEnum::DELETE,
                    ExtActionEnum::TEST,
                    ExtActionEnum::TEST2,
                ],
                ResourceEnum::USER     => $baseList,
            ],
            $factory->getAllowedList(),
        );
    }

    /**
     * @throws Exception
     */
    public function testAllowedActions(): void
    {
        /** @var MaskFactory $factory */
        $factory = self::getContainer()->get('hbpf.factory.mask');
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST, ResourceEnum::FILE));
        self::assertTrue($factory->isActionAllowed(ActionEnum::READ, ResourceEnum::TOKEN));
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::TOKEN));
        self::assertFalse($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::USER));
    }

    /**
     * @throws Exception
     */
    public function testPropertiesFromMask(): void
    {
        /** @var MaskFactory $factory */
        $factory = self::getContainer()->get('hbpf.factory.mask');

        self::assertSame(PropertyEnum::GROUP, $factory::getPropertyFromMask(2));
        self::assertSame(PropertyEnum::OWNER, $factory::getPropertyFromMask(1));

        self::assertEquals(['read', 'delete', 'test2'], $factory->getActionsFromMask(21));
        self::assertEquals(
            ['read', 'delete', 'test2'],
            $factory->getActionsFromMask(
                //@codingStandardsIgnoreLine
                $factory->maskAction(['read', 'write' => FALSE, 'delete', 'test2'], ResourceEnum::TOKEN),
            ),
        );
        self::assertEquals(
            ['write', 'test', 'test2'],
            MaskFactory::getActionsFromMaskStatic(26, ExtActionEnum::getChoices()),
        );
    }

    /**
     * @throws Exception
     */
    public function testMaskActionZero(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::ZERO_MASK);
        $f = new MaskFactory(ActionEnum::class, ResourceEnum::class);
        $f->maskAction([], ResourceEnum::GROUP);
    }

    /**
     * @throws Exception
     */
    public function testMissingData(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::MISSING_DATA);
        MaskFactory::maskProperty([]);
    }

    /**
     * @throws Exception
     */
    public function testMissingValue(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::ZERO_MASK);
        MaskFactory::maskProperty(
            [
                PropertyEnum::GROUP => [],
                PropertyEnum::OWNER => [],
            ],
        );
    }

    /**
     * @throws Exception
     */
    public function testMaskFromYaml(): void
    {
        $f = new MaskFactory(
            ActionEnum::class,
            ResourceEnum::class,
            [],
        );

        $res = $f->maskActionFromYmlArray(
            [
                ActionEnum::READ,
                ActionEnum::DELETE,
            ],
            ResourceEnum::GROUP,
        );

        self::assertSame(5, $res);
    }

    /**
     * @return void
     */
    public function test3(): void
    {
        self::expectException(LogicException::class);
        MaskFactory::getPropertyFromMask(3);
    }

}
