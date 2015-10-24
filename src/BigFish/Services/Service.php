<?php
/**
 * BigFish Service base class.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Services;

use BigFish\CardboardBox;

abstract class Service {

    /** @var CardboardBox Application dependencies container. */
    protected $app;

    /**
     * Constructor.
     *
     * @param CardboardBox  The dependencies container.
     */
    public function __construct(CardboardBox $app) {
        $this->app = $app;
    }
}
