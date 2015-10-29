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

    /** @var The request input from $_GET, $_POST or a JSON body. */
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
        $this->unhandled = explode('/', substr($this->request->getPathInfo(), 1));
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
     * Get a Uri.
     *
     * @return  string  The URI.
     */
    public function getUrl() {
        return $this->request->getUriForPath(null);
    }

    /**
     * Handle the path.
     *
     * @param  mixed    - null (default) returns all parts as an array
     *                  - true returns the whole path as a string, or null if empty
     *                  - an integer returns an array of that length, padded with nulls
     * @param  boolean  Set to true to get the path without confirming it as handled.
     * @return string|array  The path.
     */
    public function handle($count = null, $test = false) {
        if (null === $count || true === $count || ($diff = $count - count($this->unhandled)) === 0) {
            // handle the whole path
            if ($count === true) {
                $handled = implode('/', $this->unhandled);
                if ($handled === '') {
                    $handled = null;
                }
            } else {
                $handled = $this->unhandled;
            }
            if (!$test) {
                $this->unhandled = [];
            }
            return $handled;
        } elseif ($diff < 0) {
            // not handling the whole path
            $handled = array_slice($this->unhandled, 0, $count);
            if (!$test) {
                $this->unhandled = array_slice($this->unhandled, $count);
            }
            return $handled;
        } else {
            // $diff > 0 so asking for more than in the path
            $handled = array_merge($this->unhandled, array_fill(0, $diff, null));
            if (!$test) {
                $this->unhandled = [];
            }
            return $handled;
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

    /**
     * Check path handling.
     *
     * @return boolean  True iff the path has been fully handled.
     */
    public function isXhr() {
        return $this->request->isXmlHttpRequest();
    }
}
