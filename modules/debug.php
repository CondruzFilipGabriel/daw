<?php
    class Debug {
        private static $logFile = __DIR__ . '/../errors.log'; // calea catre fisierul cu loguri

        public static function log($message) {
            // Ne asiguram ca fisierul poate fi scris
            if (!is_writable(self::$logFile)) {
                error_log("Debug log file is not writable: " . self::$logFile);
                return;
            }

            // Numele fisierului si linia de cod unde s-a generat eroarea
            $backtrace = debug_backtrace();
            $callerFile = isset($backtrace[0]['file']) ? basename($backtrace[0]['file']) : 'Fisier necunoscut';
            $callerLine = isset($backtrace[0]['line']) ? $backtrace[0]['line'] : 'Linie necunoscuta';

            // Convertim mesajul intr-o forma citibila
            $logMessage = is_string($message) ? $message : json_encode($message, JSON_PRETTY_PRINT);

            // Adaugam timpul, fisierul care a trigger-uit eroarea si mesajul
            file_put_contents(
                self::$logFile,
                "[" . date('Y-m-d H:i:s') . "] [$callerFile:$callerLine] " . $logMessage . PHP_EOL,
                FILE_APPEND
            );
        }
    }
?>
