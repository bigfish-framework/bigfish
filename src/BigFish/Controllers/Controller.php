<?php
/**
 * BigFish Service base class.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Controllers;

use BigFish\CardboardBox;
use BigFish\Request;
use BigFish\Response;

abstract class Controller {

    /** var  CardboardBox  The application dependencies container. */
    protected $app;
    
    /** var  string  The name used to invoke the controller. */
    protected $name;

    /** var  Request  The request the controller is handling. */
    protected $request;

    /**
     * Constructor.
     *
     * @param  CardboardBox  The application dependencies container.
     * @param  Request       The request the controller is handling.
     * @param  string        The name used to invoke the controller.
     */
    public function __construct(CardboardBox $app, Request $request, $name = null) {
        $this->app = $app;
        $this->name = $name;
        $this->request = $request;
    }

    /**
     * Render a page.
     *
     * @param  string  Request  The request to handle.
     * @return Response  The page as a Response object.
     */
    protected function render($page, $vars = []) {
        $vars = array_merge([
            'url' => $this->request->getUrl(),
            'assetsUrl' => $this->request->getUrl().'/assets',
        ], $vars);
        $body = $this->app->twig->render($page, $vars);
        return new Response($this->app, $body);
    }
}
