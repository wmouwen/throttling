<?php

namespace Sqlr\Throttling\Tests\Storage;

use Sqlr\Throttling\Storage\Once;

class OnceTest extends \PHPUnit\Framework\TestCase
{
    /** @var Once */
    protected $storage;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->storage = new Once();
    }

    public function testSetAndGet()
    {
        $this->storage->set('foo', 'bar');

        $this->assertEquals('bar', $this->storage->get('foo'));
        $this->assertEquals(null, $this->storage->get('bar'));
    }

    public function testOverride()
    {
        $this->storage->set('foo', 'bar');

        $this->assertEquals('bar', $this->storage->get('foo'));

        $this->storage->set('foo', 'baz');

        $this->assertEquals('baz', $this->storage->get('foo'));
    }
}
