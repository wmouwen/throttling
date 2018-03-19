<?php

namespace Sqlr\Throttling\Tests;

use Sqlr\Throttling\LeakyBucket;

class LeakyBucketTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeakyBucket */
    protected $leakyBucket;

    /**
     * @inheritdoc
     */
    public function setUp() {
        $this->leakyBucket = new LeakyBucket();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(LeakyBucket::class, $this->leakyBucket);
    }
}
