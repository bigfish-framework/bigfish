<?php
/**
 * BigFish response.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
**/

namespace BigFish;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BigFish\HttpException;
use BigFish\Response;
use BigFish\Services\FactoryInterface;

class Response implements FactoryInterface {

    /** Application dependencies container. */
    protected $app;

    /** API errors. */
    protected $apiErrors;

    /** Data for API responses. */
    protected $data;

    /** Error message for API responses. */
    protected $message;

    /** Redirect URL. */
    protected $redirect;

    /** Symfony response instance. */
    public $response;

    /**
     * Constructor.
    **/
    public function __construct($app, $body = null) {
        $this->app = $app;
        $this->response = new HttpFoundation\Response;
        if ($body !== null) {
            $this->setBody($body);
        }
    }

    /**
     * Set status.
    **/
    public function setStatus($status) {
        $this->response->setStatusCode($status);
        return $this; // chainable
    }

    /**
     * Set a header.
    **/
    public function setHeader($name, $value) {
        $this->response->headers->set($name, $value);
        return $this; // chainable
    }

    /**
     * Set data.
    **/
    public function setData($data) {
        $this->data = $data;
        $this->isApi = true;
        return $this; // chainable
    }

    /**
     * Set body.
    **/
    public function setBody($body) {
        $this->response->setContent($body);
        return $this; // chainable
    }

    /**
     * Set redirect location.
    **/
    public function setRedirect($url, $status = 302) {
        if (substr($url, 0, 4) !== 'http') {
            $url = $this->app->request->getUri($url);
        }
        $this->response = new RedirectResponse($url, $status);
        return $this; // chainable
    }

    /**
     * Send the response to a request.
    **/
    public function send(Request $request) {
        if (is_a($this->response, RedirectResponse::class)) {
        } elseif ($this->data !== null) {
            $syRequest = $request->getRequest();
            if ($syRequest->query->has('callback')) {
                $this->response->headers->set('Content-Type', 'text/javascript');
                $this->response->setContent($syRequest->query->get('callback')
                    . '(' . json_encode($this->data) . ');');
            } else {
                if ($request->acceptsJson(true)) {
                    $this->response->headers->set('Content-Type', 'application/json');
                    $pretty = null;
                } else {
                    $this->response->headers->set('Content-Type', 'text/plain');
                    $pretty = JSON_PRETTY_PRINT;
                }
                $this->response->setContent(json_encode($this->data, $pretty));
            }
        }
        $this->response->prepare($request->getRequest());
        $this->response->send();
    }
}
