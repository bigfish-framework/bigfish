<?php
/**
 * BigFish Exception.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish;

class Exception extends \Exception {

    /** @var array The context for an error. */
    public $context;

    /** @var boolean Flag an error. */
    public $isError;

    /** @var boolean Flag a fatal error. */
    public $isFatal;

    /** @var int The severity level for an error. */
    public $severity;

    /**
     * Constructor.
     *
     * @param  string|array  Message text or array of message text with placeholders
     *                       for variable substitution and variables to substitute.
     * @param  Exception     Previous exception.
     */
    public function __construct($message = null, $previous = null) {
        if (is_array($message)) {
            if (count($message) > 1) {
                $message = strtr(array_shift($message), $message);
            } else {
                $message = $message[0];
            }
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * Turn an error into an exception.
     *
     * @param  array  The error thrown by PHP.
     */
    public function fromError($severity, $message, $file, $line, $context = null) {
        $this->isError = true;
        $this->severity = $severity;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
        $this->code = 'Error';
        return $this;
    }

    /**
     * Turn a fatal error into an exception.
     *
     * @param  array  The error thrown by PHP.
     *
     * @codeCoverageIgnore
     */
    public function fromFatal($err) {
        $this->isFatal = true;
        $this->fromError($err['type'], $err['message'], $err['file'], $err['line']);
        $this->code = 'Fatal error';
        return $this;
    }

}
