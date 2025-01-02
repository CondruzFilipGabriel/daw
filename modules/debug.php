<?php
    class Debug {
        private static $logFile = __DIR__ . '/../errors.log'; // Path to the log file

        public static function log($message) {
            // Ensure the log file is writable
            if (!is_writable(self::$logFile)) {
                error_log("Debug log file is not writable: " . self::$logFile);
                return;
            }

            // Get the file and line that triggered the log
            $backtrace = debug_backtrace();
            $callerFile = isset($backtrace[0]['file']) ? basename($backtrace[0]['file']) : 'Unknown file';
            $callerLine = isset($backtrace[0]['line']) ? $backtrace[0]['line'] : 'Unknown line';

            // Convert the message to a readable format
            $logMessage = is_string($message) ? $message : json_encode($message, JSON_PRETTY_PRINT);

            // Append the timestamp, caller info, and log message
            file_put_contents(
                self::$logFile,
                "[" . date('Y-m-d H:i:s') . "] [$callerFile:$callerLine] " . $logMessage . PHP_EOL,
                FILE_APPEND
            );
        }
    }
?>
