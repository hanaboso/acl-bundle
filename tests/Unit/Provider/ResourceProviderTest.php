<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Provider;

use Exception;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class ResourceProviderTest
 *
 * @package AclBundleTests\Unit\Provider
 */
final class ResourceProviderTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testGetResources(): void
    {
        $resourceProvider = new ResourceProvider(
            [
                'resources' => [
                    'one' => 'One',
                    'two' => 'Two',
                ],
            ],
        );

        self::assertEquals(
            [
                'one' => 'One',
                'two' => 'Two',
            ],
            $resourceProvider->getResources(),
        );
    }

    /**
     * @throws Exception
     */
    public function testGetResourcesMissing(): void
    {
        self::expectException(ResourceProviderException::class);
        self::expectExceptionCode(ResourceProviderException::RULESET_NOT_EXIST);

        new ResourceProvider([]);
    }

    /**
     * @throws Exception
     */
    public function testGetResourcesNotArray(): void
    {
        self::expectException(ResourceProviderException::class);
        self::expectExceptionCode(ResourceProviderException::RULESET_NOT_EXIST);

        new ResourceProvider(['resources' => new stdClass()]);
    }

    /**
     * @throws Exception
     */
    public function testHasResource(): void
    {
        $resourceProvider = new ResourceProvider(
            [
                'resources' => [
                    'one' => 'One',
                ],
            ],
        );

        self::assertTrue($resourceProvider->hasResource('one'));
        self::assertFalse($resourceProvider->hasResource('two'));
    }

    /**
     * @throws Exception
     */
    public function testGetResource(): void
    {
        $resourceProvider = new ResourceProvider(
            [
                'resources' => [
                    'one' => 'One',
                ],
            ],
        );

        self::assertSame('One', $resourceProvider->getResource('one'));
    }

    /**
     * @throws Exception
     */
    public function testGetResourceMissing(): void
    {
        self::expectException(ResourceProviderException::class);
        self::expectExceptionCode(ResourceProviderException::RESOURCE_NOT_EXIST);

        (new ResourceProvider(
            [
                'resources' => [
                    'one' => 'One',
                ],
            ],
        ))->getResource('two');
    }

}
