<?php

App::uses('ErrorHandler', 'Error');

/**
 * Class AppError
 * Custom application error handler
 */

class JsonErrorHandler extends ErrorHandler
{
    public static function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        // get the error code
        list(, $level) = ErrorHandler::mapErrorCode($code);

        // Don't error out if reporting is 0
        if (error_reporting() === 0) {
            return false;
        }

        // Get the error config setup
        $errorConfig = Configure::read('Error');
        list($error, $log) = self::mapErrorCode($code);

        // Is the error fatal?
        if ($log === LOG_ERR) {
            return self::handleFatalError($code, $description, $file, $line);
        }

        // If debugging is enabled we throw a fatal for EVERYTHING
        $debug = Configure::read('debug');
        if ($debug) {
            $data = array(
                'level' => $log,
                'code' => $code,
                'error' => $error,
                'description' => $description,
                'file' => $file,
                'line' => $line,
                'context' => $context,
                'start' => 2,
                'path' => Debugger::trimPath($file)
            );
            return self::handleFatalError($code, $description, $file, $line);
        }

        // Log and return
        $message = $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
        return CakeLog::write($log, $message);
    }

}