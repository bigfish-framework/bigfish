<?php
/**
 * BigFish request.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish;

use BigFish\Services\Service;
use BigFish\Services\FactoryInterface;
use BigFish\Exception;
use Symfony\Component\HttpFoundation;

class Request extends Service implements FactoryInterface {

    /** @const Request types. */
    const TYPE_HTTP = 1;

    /** @var The type of request. */
    public $type;

    /** @var The Symfony Request object. */
    protected $input;

    /** @var The Symfony Request object. */
    protected $request;

    /** @var Parts of the path that have not yet been handled. */
    protected $unhandled;

    /**
     * Create a request.
     *
     * The request is normally created from PHP globals but if an array is passed
     * it will be used instead - useful for testing.
     *
     * @param  array  If supplied, values to use for the request.
     * @return $this  Chainable.
     */
    public function create($globals = null) {
        if (is_null($globals)) {
            $this->type = static::TYPE_HTTP;
            $this->request = HttpFoundation\Request::createFromGlobals();
        } else {
            throw new HttpException;
        }
        $this->unhandled = explode('/', $this->request->getPathInfo());
        return $this;
    }

    public function getInput() {
        if ($this->input !== null) {
            return $this->input;
        } else {
            // lazy load it
            if (strpos($this->request->headers->get('Content-Type'), 'application/json') === 0) {
                try {
                    $this->input = json_decode($this->request->content);
                } catch (\Exception $e) {
                    throw new HttpException('Malformed JSON body', 400);
                }
            } elseif ($this->input === null) {
                if ($this->request->getRealMethod() === 'GET') {
                    $this->input = $this->request->query;
                } else {
                    $this->input = $this->request->request;
                }
            }
            return $this->input;
        }
    }

    /**
     * Get the request method.
     *
     * @return  string
     */
    public function getMethod() {
        return $this->request->getMethod();
    }

    /**
     * Get the Symfony request object.
     *
     * @return  HttpFoundation\Request  The Symfony request object.
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Handle the path.
     *
     * @param  true    True returns the whole unhandled path.
     * @return string  The path.
     */
    public function handle($arg) {
        if (true === $arg) {
            $this->unhandled = [];
            $path = implode('/', $this->unhandled);
            return $path;
        }
    }
    /**
     * Check path handling.
     *
     * @return boolean  True iff the path has been fully handled.
     */
    public function isHandled() {
        return $this->unhandled === [];
    }
}
