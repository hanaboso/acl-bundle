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

/**
 * Class MaskFactoryTest
 *
 * @package AclBundleTests\Unit\Factory
 *
 * @covers  \Hanaboso\AclBundle\Factory\MaskFactory
 */
final class MaskFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskAction()
     * @throws Exception
     */
    public function testMaskAction(): void
    {
        $factory = self::$container->get('hbpf.factory.mask');
        $data    = [
            'read'   => FALSE,
            'write',
            'delete' => 'true',
        ];

        self::assertEquals(6, $factory->maskAction($data, ResourceEnum::TOKEN));
    }

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskProperty()
     * @throws Exception
     */
    public function testMaskProperty(): void
    {
        $data = [
            'owner' => '1',
            'group' => 1,
        ];

        self::assertEquals(2, MaskFactory::maskProperty($data));
    }

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::getAllowedList()
     *
     * @throws Exception
     */
    public function testAllowedList(): void
    {
        $factory  = self::$container->get('hbpf.factory.mask');
        $baseList = [
            ExtActionEnum::READ,
            ExtActionEnum::WRITE,
            ExtActionEnum::DELETE,
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
            $factory->getAllowedList(FALSE)
        );

        self::assertEquals(
            [
                ResourceEnum::TOKEN    => [
                    ExtActionEnum::READ,
                    ExtActionEnum::WRITE,
                    ExtActionEnum::DELETE,
                    ExtActionEnum::TEST,
                    ExtActionEnum::TEST2,
                ],
                ResourceEnum::GROUP    => $baseList,
                ResourceEnum::USER     => $baseList,
                ResourceEnum::FILE     => $baseList,
                ResourceEnum::RULE     => $baseList,
                ResourceEnum::TMP_USER => $baseList,
            ],
            $factory->getAllowedList()
        );
    }

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::isActionAllowed()
     *
     * @throws Exception
     */
    public function testAllowedActions(): void
    {
        /** @var MaskFactory $factory */
        $factory = self::$container->get('hbpf.factory.mask');
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST, ResourceEnum::FILE));
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::READ, ResourceEnum::TOKEN));
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::TOKEN));
        self::assertFalse($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::USER));
    }

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::getActionsFromMask()
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::getActionsFromMaskStatic()
     *
     * @throws Exception
     */
    public function testPropertiesFromMask(): void
    {
        /** @var MaskFactory $factory */
        $factory = self::$container->get('hbpf.factory.mask');

        self::assertEquals(PropertyEnum::GROUP, $factory::getPropertyFromMask(2));
        self::assertEquals(PropertyEnum::OWNER, $factory::getPropertyFromMask(1));

        self::assertEquals(['read', 'delete', 'test2'], $factory->getActionsFromMask(21));
        self::assertEquals(
            ['read', 'delete', 'test2'],
            $factory->getActionsFromMask(
                $factory->maskAction(['read', 'write' => FALSE, 'delete', 'test2'], ResourceEnum::TOKEN)
            )
        );
        self::assertEquals(
            ['write', 'test', 'test2'],
            $factory->getActionsFromMaskStatic(26, ExtActionEnum::getChoices())
        );
    }

    /**
     * @throws Exception
     *
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskAction
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
     *
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskProperty
     */
    public function testMissingData(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::MISSING_DATA);
        MaskFactory::maskProperty([]);
    }

    /**
     * @throws Exception
     *
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskProperty
     */
    public function testMissingValue(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::ZERO_MASK);
        MaskFactory::maskProperty(
            [
                PropertyEnum::OWNER => [],
                PropertyEnum::GROUP => [],
            ]
        );
    }

    /**
     * @throws Exception
     *
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::maskActionFromYmlArray
     */
    public function testMaskFromYaml(): void
    {
        $f = new MaskFactory(
            ActionEnum::class,
            ResourceEnum::class,
            []
        );

        $res = $f->maskActionFromYmlArray(
            [
                ActionEnum::READ,
                ActionEnum::DELETE,
            ],
            ResourceEnum::GROUP
        );

        self::assertEquals(5, $res);
    }

    /**
     * @covers \Hanaboso\AclBundle\Factory\MaskFactory::getPropertyFromMask
     */
    public function test3(): void
    {
        self::expectException(LogicException::class);
        MaskFactory::getPropertyFromMask(3);
    }

}
