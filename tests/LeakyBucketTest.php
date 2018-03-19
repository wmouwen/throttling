<?php

namespace Sqlr\Throttling\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Sqlr\Throttling\LeakyBucket;
use Sqlr\Throttling\Storage\StorageInterface;
use Sqlr\Throttling\Throttler;

class LeakyBucketTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LeakyBucket
     */
    protected $leakyBucket;

    /**
     * @var MockObject|StorageInterface
     */
    protected $storage;

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function setUp()
    {
        $this->storage = $this->createMock(StorageInterface::class);

        $this->leakyBucket = new LeakyBucket($this->storage);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(Throttler::class, $this->leakyBucket);
    }

    public function testSetters()
    {
        $this->leakyBucket
            ->setLocking(true)
            ->setKey('testkey')
            ->setCapacity(1337)
            ->setPeriod(86400);

        $this->assertTrue($this->leakyBucket->isLocking());
        $this->assertEquals(1337, $this->leakyBucket->getCapacity());
        $this->assertEquals('testkey', $this->leakyBucket->getKey());
        $this->assertEquals(86400, $this->leakyBucket->getPeriod());
    }

    public function testNothingInStorageYet()
    {
        $this->storage
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue(null));

        $this->leakyBucket
            ->setCapacity(100)
            ->setPeriod(60);

        $this->assertTrue($this->leakyBucket->available(40));
    }

    public function testResourcesAvailable()
    {
        $this->storage
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [$this->leakyBucket->getKey() . '-time', microtime(true) - 5],
                [$this->leakyBucket->getKey() . '-ratio', 0.3],
            ]));

        $this->leakyBucket
            ->setCapacity(100)
            ->setPeriod(60);

        $this->assertTrue($this->leakyBucket->available(40));
    }

    public function testResourcesBarelyAvailable()
    {
        $this->storage
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [$this->leakyBucket->getKey() . '-time', microtime(true) - 30],
                [$this->leakyBucket->getKey() . '-ratio', 1.0],
            ]));

        $this->leakyBucket
            ->setCapacity(100)
            ->setPeriod(60);

        $this->assertTrue($this->leakyBucket->available(50));
    }

    public function testResourcesBarelyUnavailable()
    {
        $this->storage
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [$this->leakyBucket->getKey() . '-time', microtime(true) - 29.999],
                [$this->leakyBucket->getKey() . '-ratio', 1.0],
            ]));

        $this->leakyBucket
            ->setCapacity(100)
            ->setPeriod(60);

        $this->assertFalse($this->leakyBucket->available(50));
    }

    public function testResourcesUnavailable()
    {
        $this->storage
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                [$this->leakyBucket->getKey() . '-time', microtime(true) - 5],
                [$this->leakyBucket->getKey() . '-ratio', 0.75],
            ]));

        $this->leakyBucket
            ->setCapacity(100)
            ->setPeriod(60);

        $this->assertFalse($this->leakyBucket->available(40));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAvailableWithNegativeResources()
    {
        $this->leakyBucket->available(-1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAvailableWithTooMuchResources()
    {
        $this->leakyBucket->setCapacity(10);

        $this->leakyBucket->available(11);
    }
}
