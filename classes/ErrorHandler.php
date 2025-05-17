<?php
class ErrorHandler {
    private static $instance = null;
    private $logFile;

    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/error.log';
        $this->initializeErrorHandling();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeErrorHandling() {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        $error = date('[Y-m-d H:i:s]') . " Error: [$errno] $errstr in $errfile on line $errline\n";
        error_log($error, 3, $this->logFile);

        if (ini_get('display_errors')) {
            return false; // Let PHP handle the error display if enabled
        }
        return true;
    }

    public function handleException($exception) {
        $error = date('[Y-m-d H:i:s]') . ' Exception: ' . $exception->getMessage() .
                ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
        error_log($error, 3, $this->logFile);

        $this->displayError('An unexpected error occurred. Please try again later.');
    }

    public function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private function displayError($message) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }
}