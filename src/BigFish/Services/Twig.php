<?php

/**
 * BigFish service wrapper.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
 *
 * @TODO refactor to add default extension and anonymise as $app->template
**/

namespace BigFish\Services;

use BigFish\Services\Service;
use BigFish\HttpException;
use Twig_Error_Loader;

class Twig extends Service {

    /** The Twig instance. */
    protected $twig;

    /** The directory to use for cached templates. */
    protected $cacheDir;

    /** Twig environment options. */
    protected $environmentOptions = [];

    /** Global template variables. */
    protected $globals = [];

    /**
     * Constructor.
    **/
    public function __construct($app) {
        parent::__construct($app);
        $this->viewsDir = [
            $app->get('twig.templatesDir'),
            $app->get('bigfish.basedir') . '/views',
        ];
        $this->cacheDir = $app->get('local.cacheDir');

        $this->environmentOptions = [
            'cache' => $app->get('local.cacheDir').'twig',
            'auto_reload' => $app->get('debug') || $app->get('twig.reload'),
        ];

        $loader = new \Twig_Loader_Filesystem($this->viewsDir);
        $this->twig = new \Twig_Environment($loader, $this->environmentOptions);
        $this->globals = [
            'baseurl' => $this->app->request->getUri(null),
            'secureurl' => $this->app->request->getUri(null, true),
        ];
    }

    public function getTemplate($name) {
        try {
            return $this->twig->loadTemplate($name);
        } catch (Twig_Error_Loader $e) {
            throw new HttpException(['Template :tpl does not exist', [':tpl' => $name]]);
        }
    }

    public function render($template, $vars = []) {
        if (is_string($template)) {
            $template = $this->getTemplate($template);
        }
        $vars = array_merge($this->globals, $vars);
        return $template->render($vars);
    }
}
