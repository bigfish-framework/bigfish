<?php

/**
 * BigFish service wrapper.
 *
 * @copyright  Copyright © 2015 [MrAnchovy](www.mranchovy.com)
 * @licence    MIT
**/

namespace BigFish\Service;

use BigFish\Service;
use Monolog\Logger;
use Monolog\Handler;

class Monolog extends Service {

    const HANDLER_STREAM = 1;
    const HANDLER_ROTATE = 2;

    /** Monolog instances. */
    protected $loggers = [];

    public function getLogger($name, $options = null) {
        if (!isset($this->loggers[$name])) {
            // Create the logger
            $logger = new Logger($name);
            // Now add some handlers
            $logPath = $this->app->getConfig('local.logsDir')."$name/$name.log";
            if ($options && self::HANDLER_ROTATE) {
                $logger->pushHandler(new Handler\RotatingFileHandler($logPath));
            } else {
                $logger->pushHandler(new Handler\StreamHandler($logPath));
            }
            $this->loggers[$name] = $logger;
        }
        return $this->loggers[$name];
    }

}
