<?php declare(strict_types=1);

namespace Tests\Unit\Provider;

use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class ResourceProviderTest
 *
 * @package Tests\Unit\Provider
 */
class ResourceProviderTest extends TestCase
{

    /**
     * @covers ResourceProvider::getResources()
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
     */
    public function testGetResourcesMissing(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionCode(UserException::RULESET_NOT_EXIST);

        new ResourceProvider([]);
    }

    /**
     * @covers ResourceProvider::getResources()
     */
    public function testGetResourcesNotArray(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionCode(UserException::RULESET_NOT_EXIST);

        new ResourceProvider(['resources' => new stdClass()]);
    }

    /**
     * @covers ResourceProvider::hasResource()
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
     */
    public function testGetResourceMissing(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionCode(UserException::RESOURCE_NOT_EXIST);

        (new ResourceProvider([
            'resources' => [
                'one' => 'One',
            ],
        ]))->getResource('two');
    }

}