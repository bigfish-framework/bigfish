<?php
/**
 * BigFish error controller.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Controllers;

use BigFish\Exception;
use BigFish\Response;
use BigFish\HttpException;
use BigFish\Controllers\Controller;
use Kint;

class ErrorController extends Controller {

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
        return $this->getPageResponse($e);
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
    public function getPageResponse($e) {
        $redirect = false; // $e->getRedirect();
        if ($redirect) {
            return (new Response($this->app))->setRedirect($redirect === true ? '/' : $redirect);
        }
        $status = is_a($e, HttpException::class) ? $e->getStatus() : 500;
        $vars = [
            'status' => $status,
            'e'      => $this->app->get('app.debug') ? $e : false,
            ];
        $response = $this->render('pages/error.html.twig', $vars);
        try {
            $response->setStatus($status);
        } catch (\Exception $e) {
            $response->setStatus(500);
        }
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
