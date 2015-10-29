<?php
/**
 * BigFish service wrapper.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
 *
 */
namespace BigFish\Services;

use BigFish\Services\Service;
use BigFish\Exception;
use BigFish\HttpException;
use Twig_Error_Loader;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

class Twig extends Service {

    /** The Twig instance. */
    protected $twig;

    /** Twig environment options. */
    protected $environmentOptions = [];

    /** Global template variables. */
    protected $globals = [];

    /**
     * Constructor.
    **/
    public function __construct($app) {
        parent::__construct($app);
        $this->viewsDir = $app->get('twig.templatesDir');

        $this->environmentOptions = [
            'cache' => $app->get('local.cacheDir').'/twig',
            'auto_reload' => $app->get('app.debug') || !$app->get('twig.noreload'),
            'strict_variables' => $app->get('app.debug') || !$app->get('twig.strict'),
        ];
        $loader = new \Twig_Loader_Filesystem($app->get('twig.templatesDir'));
        $this->twig = new \Twig_Environment($loader, $this->environmentOptions);
        $this->globals = [
            'html' => '<script>document.write("Added by script")</script>',
            'baseurl' => $this->app->request->getRequest()->getUriForPath(null),
        ];

        $engine = new MarkdownEngine\ParsedownEngine();
        $this->twig->addExtension(new MarkdownExtension($engine));
        
    }

    public function getTemplate($name) {
        try {
            return $this->twig->loadTemplate($name);
        } catch (Twig_Error_Loader $e) {
            throw new HttpException(['Could not load template "@0": @1', $name, $e->getMessage()]);
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
