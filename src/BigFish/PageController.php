<?php
/**
 * BigFish request.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish;

use BigFish\Services\Service;
use BigFish\Exception;
use BigFish\Request;
use BigFish\Response;

class PageController extends Service {

    /**
     * Respond to a GET request for a page.
     *
     * @param  Request  The request to handle.
     * @return Response  The page as a Response object.
     */
    public function routeGet(Request $request) {
        // handle the whole of the path
        $page = $request->handle(true);
        if ($page === '') {
            $page = 'pages/index.html.twig';
        } else {
            // need to be careful about characters in pages to avoid paths like /page/../secret
            if (!preg_match('|^[A-Za-z][A-Za-z0-9\\/]*$|', $page)) {
                throw new HttpException(['Illegal characters in page name [0]', $page]);
            }
            $page = "pages/content/$page.html.twig";
        }
        return $this->render($page);
    }

    /**
     * Render a page.
     *
     * @param  string  Request  The request to handle.
     * @return Response  The page as a Response object.
     */
    protected function render($page) {
        // $body = $this->app->twig->render($page);
        $body = 'Hello World';
        return new Response($this->app, $body);
    }
}
