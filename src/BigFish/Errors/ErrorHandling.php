<?php
/**
 * BigFish error handling.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish\Errors;

use BigFish\Services\Service;
use BigFish\Exception;
use BigFish\HttpException;
use Kint;

class ErrorHandling extends Service {

    /**
     * Initialise.
     *
     * @codeCoverageIgnore
     */
    public function init() {
        error_reporting(-1);
        ini_set('display_errors', 0);
        set_error_handler(array($this, 'handleError'), -1);
        set_exception_handler(array($this, 'handleException'));
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Error handler.
     */
    public function handleError($severity, $message, $file, $line, $context = []) {
        throw (new Exception)
            ->fromError($severity, $message, $file, $line, $context);
    }

    /**
     * Exception handler.
     */
    public function handleException($e) {
        if (!is_a($e, HttpException::class)) {
            $this->logError($e);
        }
        $this->renderException($e);
    }

    /**
     * Shutdown handler.
     *
     * @codeCoverageIgnore
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
            // this should only happen if there was a fatal error
            $e = (new Exception)->fromFatal($error);
            $this->handleException($e);
        }
    }

    /**
     * Error logger.
     */
    protected function logError($e) {
        try {
            $logger = $this->app->logger->getLogger('errors');
            // $logger = $this->app->monolog->getLogger('errors', \BigFish\Services\Monolog::HANDLER_ROTATE);
            $logger->addInfo($e->getMessage(), [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);
            // flush the log
            $logger->close();
        } catch (\Throwable $e) {
            // PHP 7 compatibility
        } catch (\Exception $e) {
        }
    }

    /**
     * Render an exception.
     */
    protected function renderException($e, $buffer = null) {
        $message = htmlspecialchars($e->getMessage());
        $line = $e->getLine();
        $file = htmlspecialchars($e->getFile());
        $error = htmlspecialchars($e->getCode());
        $class = get_class($e);
        if (!is_string($error) || '0' === $error) {
            $error = "error $error";
        }
        $trace = $e->getTrace();
        // put the file details in the stack trace
        if(empty($e->isError)) {
            array_unshift($trace, ['file' => $file, 'line' => $line, 'function' => '', 'args' => []]);
        } else {
            // this replaces the error handler in the trace
            $trace[0] = ['file' => $file, 'line' => $line, 'function' => '', 'args' => []];
        }

        if (null !== ($previous = $e->getPrevious())) {
             $deeper = '<div style="padding-left: 20px">'
                . $this->renderException($previous, true)
                . '</div>';
            $trace = '';
        } else {
            $deeper = '';
            $trace = php_sapi_name() === 'cli' ? '' : @Kint::trace($trace);
        }

        $out = "<div style='font-family: sans-serif;'>\n"
            . "<h3>$class $error</h3>\n"
            . "<p style='color: red;'><strong>$message</strong></p>\n"
            . "<p><small>Line $line of $file</small></p>\n"
            . $trace
            . "</div>"
            . $deeper;

        if (php_sapi_name() === 'cli') {
            $out = strip_tags($out);
        }

        if ($buffer) {
            return $out;
        } else {
            echo $out;
        }
    }
}
