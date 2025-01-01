<?php
    class Debug {
        private static $logFile = __DIR__ . '/../errors.log'; // Path to the log file

        /**
         * Log a message to the errors.log file
         * 
         * @param mixed $message The message to log (string, array, object, etc.)
         * @return void
         */
        public static function log($message) {
            // Ensure the log file is writable
            if (!is_writable(self::$logFile)) {
                error_log("Debug log file is not writable: " . self::$logFile);
                return;
            }

            // Convert the message to a readable format
            $logMessage = is_string($message) ? $message : json_encode($message, JSON_PRETTY_PRINT);

            // Append the timestamp and log message
            file_put_contents(
                self::$logFile,
                "[" . date('Y-m-d H:i:s') . "] " . $logMessage . PHP_EOL,
                FILE_APPEND
            );
        }
    }
?>