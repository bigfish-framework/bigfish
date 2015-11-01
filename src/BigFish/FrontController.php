<?php
/**
 * BigFish FrontController.
 *
 * Note this does NOT extend `Bigfish\Controllers\Controller` because it is not a
 * controller!
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
**/

namespace BigFish;

use BigFish\Services\Service;
use BigFish\CardboardBox;
use BigFish\Request;
use BigFish\HttpException;
use BigFish\Controllers\PageController;

class FrontController extends Service {

    /**
     * Get the response to a request.
     *
     * @param  Request
     * @return Response
    **/
    public function execute(Request $request) {
        // speculatively handle the first part of the path
        $name = $request->handle(1, true)[0];
        if ($name === ['']) {
            $controller = $this->getDefaultController($request);
        } else {
            $controller = $this->getController($request, $name);
            if ($controller === false) {
                $controller = $this->getDefaultController($request);
            } else {
                // successfully handled
                $request->handle(1);
            }
        }
        $method = $this->getControllerMethod($request, $controller);
        if ($method === false) {
            throw new HttpException([
                'The @0 controller does not respond to @1 requests', $name, $request->getMethod()]);
        }
        $response = $controller->$method();
        if ($request->isHandled()) {
            $response->send($request);
        } else {
            throw new HttpException('Path [0] of the request was not handled', $request->handle(true));
        }

    }

    /**
     * Get the controller for a request.
     *
     * @param  Request
     * @return Response
    **/
    protected function getController(Request $request, $name) {
        // see if the controller exists
        $class = $this->app->get("app.controllers.$name");
        if (class_exists($class)) {
            $controller = new $class($this->app, $request, $name);
            // check for case consistency
            $r = new \ReflectionClass($controller);
            if ($r->getName() === $class) {
                return $controller;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get the default controller.
     *
     * @param  string      The name to invoke it as.
     * @return Controller  An instance of the default controller class.
    **/
    protected function getDefaultController(Request $request, $name = null) {
        $class = $this->app->get('app.defaultController', PageController::class);
        return new $class($this->app, $request, $name);
    }

    /**
     * Get the response to a request.
     *
     * @param  Request
     * @return Response
    **/
    protected function getControllerMethod($request, $controller) {
        if ($request->isXhr()) {
            $method = "Xhr";
        } else {
            $method = ucfirst(strtolower($request->getMethod()));
        }
        $call = "route$method";
        if (is_callable([$controller, $call])) {
            return $call;
        } else {
            return false;
        }
    }
}
