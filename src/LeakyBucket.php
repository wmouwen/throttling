<?php

namespace Sqlr\Throttling;

use Sqlr\Throttling\Exception\RequestThrottled;

class LeakyBucket extends Throttler
{
    /**
     * @var string
     */
    protected $key = 'throttler-leakybucket';

    /**
     * @inheritdoc
     */
    public function available(int $resources = 1): bool
    {
        $this->assertRequestedResources($resources);

        $ratio = $this->calculateRatio(
            $resources,
            $this->getPreviousTime(),
            $this->getPreviousRatio()
        );

        return $ratio <= 1.0;
    }

    /**
     * @inheritdoc
     */
    public function claim(int $resources = 1): void
    {
        $this->assertRequestedResources($resources);

        if ($this->isLocking()) {
            $this->storage->lock($this->getKey());
        }

        $ratio = $this->calculateRatio(
            $resources,
            $this->getPreviousTime(),
            $this->getPreviousRatio()
        );

        if ($ratio > 1.0) {
            $this->storage->free($this->getKey());
            throw new RequestThrottled(ceil(($ratio - 1.0) * $this->period));
        }

        $this->setPreviousTime(microtime(true));
        $this->setPreviousRatio($ratio);

        $this->storage->free($this->getKey());
    }

    /**
     * @inheritdoc
     */
    protected function calculateRatio(
        int $resources,
        float $previousTime,
        float $previousRatio
    ): float {
        $increase = $resources / $this->capacity;
        $decrease = (microtime(true) - $previousTime) / $this->period;
        return max($previousRatio - $decrease, 0.0) + $increase;
    }

    /**
     * @return string
     */
    protected function getPreviousTimeKey(): string
    {
        return $this->getKey() . '-time';
    }

    /**
     * @return float
     */
    protected function getPreviousTime(): float
    {
        return $this->storage->get($this->getPreviousTimeKey()) ?? microtime(true);
    }

    /**
     * @param float $time
     */
    protected function setPreviousTime(float $time): void
    {
        $this->storage->set($this->getPreviousTimeKey(), $time);
    }

    /**
     * @return string
     */
    protected function getPreviousRatioKey(): string
    {
        return $this->getKey() . '-ratio';
    }

    /**
     * @return float
     */
    protected function getPreviousRatio(): float
    {
        return $this->storage->get($this->getPreviousRatioKey()) ?? 0.0;
    }

    /**
     * @param float $ratio
     */
    protected function setPreviousRatio(float $ratio): void
    {
        $this->storage->set($this->getPreviousRatioKey(), $ratio);
    }

    /**
     * @param float $ratio
     */
    protected function updatePrevious(float $ratio): void
    {
        $this->storage->set($this->getPreviousRatioKey(), $ratio);
        $this->storage->set($this->getPreviousTimeKey(), microtime(true));
    }
}
