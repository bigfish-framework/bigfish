<?php
/**
 * BigFish Service base class.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Controllers;

use BigFish\CardboardBox;

abstract class Controller {

    /** var  CardboardBox  The application dependencies container. */
    protected $app;
    
    /** var  string  The name used to invoke the controller. */
    protected $name;

    /**
     * Constructor.
     *
     * @param  CardboardBox  The application dependencies container.
     * @param  string        The name used to invoke the controller.
     */
    public function __construct(CardboardBox $app, $name) {
        $this->app = $app;
        $this->name = $name;
    }
}
