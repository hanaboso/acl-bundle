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
     * @covers ResourceProvider::getResources()
     * @throws Exception
     */
    public function testGetResources(): void
    {
        $resourceProvider = new ResourceProvider([
            'resources' => [
                'one' => 'One',
                'two' => 'Two',
            ],
        ]);

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
        ], $resourceProvider->getResources());
    }

    /**
     * @covers ResourceProvider::getResources()
     * @throws Exception
     */
    public function testGetResourcesMissing(): void
    {
        $this->expectException(ResourceProviderException::class);
        $this->expectExceptionCode(ResourceProviderException::RULESET_NOT_EXIST);

        new ResourceProvider([]);
    }

    /**
     * @covers ResourceProvider::getResources()
     * @throws Exception
     */
    public function testGetResourcesNotArray(): void
    {
        $this->expectException(ResourceProviderException::class);
        $this->expectExceptionCode(ResourceProviderException::RULESET_NOT_EXIST);

        new ResourceProvider(['resources' => new stdClass()]);
    }

    /**
     * @covers ResourceProvider::hasResource()
     * @throws Exception
     */
    public function testHasResource(): void
    {
        $resourceProvider = new ResourceProvider([
            'resources' => [
                'one' => 'One',
            ],
        ]);

        $this->assertTrue($resourceProvider->hasResource('one'));
        $this->assertFalse($resourceProvider->hasResource('two'));
    }

    /**
     * @covers ResourceProvider::getResource()
     * @throws Exception
     */
    public function testGetResource(): void
    {
        $resourceProvider = new ResourceProvider([
            'resources' => [
                'one' => 'One',
            ],
        ]);

        $this->assertSame('One', $resourceProvider->getResource('one'));
    }

    /**
     * @covers ResourceProvider::getResource()
     * @throws Exception
     */
    public function testGetResourceMissing(): void
    {
        $this->expectException(ResourceProviderException::class);
        $this->expectExceptionCode(ResourceProviderException::RESOURCE_NOT_EXIST);

        (new ResourceProvider([
            'resources' => [
                'one' => 'One',
            ],
        ]))->getResource('two');
    }

}
