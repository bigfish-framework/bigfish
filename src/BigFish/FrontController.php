<?php
/**
 * BigFish Controller base class.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
**/

namespace BigFish;

use BigFish\Services\Service;
use BigFish\CardboardBox;
use BigFish\Request;
use BigFish\HttpException;

class FrontController extends Service {

    /**
     * Get the response to a request.
     *
     * @param  Request
     * @return Response
    **/
    public function execute(Request $request) {
        $controller = $this->getController($request);
        $method = $this->getControllerMethod($request, $controller);
        $response = $controller->$method($request);
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
    protected function getController($request) {
        $path = substr($request->getRequest()->getPathInfo(), 1);
        $this->parts = explode('/', $path);
        $controller = $this->parts[0];
        if ($controller === '') {
            $class = $this->app->get('app.defaultController');
        } else {
            $class = $this->app->get('app.namespace') . 'Controller\\Controller_' . $controller;
            array_shift($this->parts);
        }
        if (class_exists($class)) {
            $c = new $class($this->app);
            $r = new \ReflectionClass($c);
            if ($r->getName() === $class) {
                return $c;
            }
        }
        $controller = $this->app->get('app.defaultController');

        return new $controller($this->app);
    }

    /**
     * Get the response to a request.
     *
     * @param  Request
     * @return Response
    **/
    protected function getControllerMethod($request, $controller) {
        $method = $request->getMethod();
        $call = "route$method";
        if (is_callable([$controller, $call])) {
            return $call;
        } else {
            throw new HttpException([
                'This controller does not respond to 0 requests', $method]);
        }
    }

    /**
     * Get the parts of a path.
     *
     * Note that a trailing slash is counted as a part and is the empty string,
     * not null.
     * 
     * @return  array  The parts imploded into an array.
    **/
    protected function deprecated_getParts($def, $extra = false) {
        $parts =& $this->parts;
        // replace empty strings with nulls - this means that trailing slashes will be ignored
        array_walk($parts, function (&$v, $k) { $v === '' && ($v = null); });
        $nDef = count($def);
        $nParts = count($parts);
        $padding = $nDef > $nParts ? array_fill(0, $nDef - $nParts, null) : [];
        if ($extra) {
            return [array_combine($def, array_merge(array_slice($parts, 0, $nDef), $padding)), array_slice($parts, $nDef)];
        } elseif ($nParts <= $nDef) {
            return array_combine($def, array_merge($parts, $padding));
        } else {
            throw new HttpException('The request path contained too many parts');
        }
    }

}
