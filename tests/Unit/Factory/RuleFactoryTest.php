<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Factory;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;

/**
 * Class RuleFactoryTest
 *
 * @package AclBundleTests\Unit\Factory
 *
 * @covers  \Hanaboso\AclBundle\Factory\RuleFactory
 */
final class RuleFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testMissingOwner(): void
    {
        self::expectException(AclException::class);
        self::expectExceptionCode(AclException::MISSING_DEFAULT_RULES);

        new RuleFactory(
            new DatabaseManagerLocator(NULL, NULL, ''),
            $this->mockResourceProvider(),
            new MaskFactory(ActionEnum::class, ResourceEnum::class, []),
            [],
            ResourceEnum::class
        );
    }

    /**
     * @throws Exception
     */
    public function testThrow(): void
    {
        self::expectException(AclException::class);

        $f = new RuleFactory(
            new DatabaseManagerLocator($this->mockDm(), NULL, 'ODM'),
            $this->mockResourceProvider(),
            new MaskFactory(ActionEnum::class, ResourceEnum::class, []),
            [
                'owner' => [
                    ResourceEnum::USER => [],
                ],
            ],
            ResourceEnum::class
        );

        $f->getDefaultRules(new Group(NULL));
    }

    /**
     * @return ResourceProvider
     */
    private function mockResourceProvider(): ResourceProvider
    {
        $r = self::createMock(ResourceProvider::class);
        $r->method('getResource')->willReturnCallback(
            static function (): void {
                throw new ResourceProviderException('');
            }
        );

        return $r;
    }

    /**
     * @return DocumentManager
     */
    private function mockDm(): DocumentManager
    {
        return self::createMock(DocumentManager::class);
    }

}
