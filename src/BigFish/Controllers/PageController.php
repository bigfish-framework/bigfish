<?php
/**
 * BigFish request.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Controllers;

use BigFish\Controllers\Controller;
use BigFish\Exception;
use BigFish\HttpException;
use BigFish\Request;
use BigFish\Response;

class PageController extends Controller {

    /**
     * Respond to a GET request for a page.
     *
     * @param  Request  The request to handle.
     * @return Response  The page as a Response object.
     */
    public function routeGet() {
        // handle the whole of the path
        $page = $this->request->handle(true);
        if ($page === null) {
            $page = 'pages/index.html.twig';
        } else {
            // need to be careful about characters in pages to avoid paths like /page/../secret
            if (!preg_match('|^[A-Za-z][A-Za-z0-9\\-\\/]*$|', $page)) {
                throw new HttpException(['Illegal characters in page name [@0]', $page]);
            }
            $page = "pages/content/$page.html.twig";
        }
        return $this->render($page);
    }
}
