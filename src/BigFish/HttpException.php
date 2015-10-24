<?php
/**
 * BigFish HTTP Exception.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
**/

namespace BigFish;
use \BigFish\Exception;

class HttpException extends Exception {

    protected $status;
    protected $errors;
    protected $redirectUrl;

    /**
     * Constructor.
     *
    **/
    public function __construct($message = 'Not found', $status = 404, $errors = null, $previous = null) {
        $this->status = (int)$status;
        if (is_array($errors)) {
            $this->errors = $errors;
        }
        parent::__construct($message, $previous);
    }

    /**
     * Get the HTTP status code.
     *
    **/
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the redirect location.
     *
    **/
    public function getRedirect() {
        return $this->redirectUrl;
    }

    /**
     * Set a redirect location.
     *
    **/
    public function setRedirect($url = true, $status = 302) {
        $this->redirectUrl = $url;
        $this->status = $status;
        return $this; // chainable
    }
}
