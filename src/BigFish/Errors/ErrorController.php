<?php
/**
 * BigFish error controller.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Errors;

use BigFish\Exception;

class ErrorController {

    protected $status = 500;

    /**
     * Get the response for an exception.
     *
     * @param  Request
     * @return Response
     */
    public function getErrorResponse($e = null) {
        if (!is_a($e, 'Exception')) {
            throw new Exception('No exception provided to error controller');
        }
        if ($e->isFatal) {
            echo $e->getMessage(),
                $e->getFile(),
                $e->getLine();
        } elseif ($e->isError) {
            Kint::trace($e->getTrace());
        } else {
            Kint::trace();
        }

        try {
            $c = new ErrorController($this->app);
            $response = $c->getErrorResponse($e);
            $response->send($this->app->request);
        } catch (\Throwable $e) {
            // PHP 7 compatibility
            echo 'Fatal error in error handler.';
        } catch (\Exception $e) {
            echo 'Fatal error in error handler.';
        }
        return;

    }

    /**
     * Get the response for an exception.
     *
     * @param  Request
     * @param  Response
     * @return Response
     */
    public function getApiResponse($request, $e) {
        $response = new Response($this->app);
        $response->setStatus($this->status);
        if (is_callable([$e, 'getErrors'])) {
            $errors = $e->getErrors();
        } else {
            $errors = null;
        }
        $response->setApiError($e->getMessage(), $errors);
        return $response;
    }

    /**
     * Get the response for an exception.
     *
     * @param  Request
     * @param  Response
     * @return Response
     */
    public function getPageResponse($request, $e) {
        $redirect = $e->getRedirect();
        if ($redirect) {
            return (new Response($this->app))->setRedirect($redirect === true ? '/' : $redirect);
        }
        $response = new Response($this->app);
        $response->setStatus($this->status);
        $benchmark = $this->app->benchmark;
        if ($benchmark) {
            $benchmark = $benchmark->getReport(true);
        }
        $response->setBody('Pretty '.$this->status.'pages<br>' . $e->getMessage() . $benchmark);
        return $response;
    }

    /**
     * Get the response for an exception.
     *
     * @param  Request
     * @param  Response
     * @return Response
     */
    public function getTextResponse($request, $e) {
        $response = new Response($this->app);
        $response->setStatus($this->status);
        $response->setBody($e->getMessage());
        return $response;
    }
}
