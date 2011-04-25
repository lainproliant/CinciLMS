<?php

class LoggerException extends Exception {
   function __construct ($message) {
      parent::__construct ($message);
   }
}

/*
 * Logger: A class to manage log file creation and usage.
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License.
 */
class Logger {
   private $logLevel;
   private $logFileName;

   function __construct ($logFileName, $logLevel)
   {
      $this->logLevel = $logLevel;
      $this->logFileName = $logFileName;

      if (! file_exists ($this->logFileName)) {
         $this->logInfo ("Log file created.");
      }

   }

   public function log ($message, $level = 0)
   {
      if ($level >= $this->logLevel) {
         $logFile = fopen ($logFileName, "a");

         if ($logFile == FALSE) {
            throw new LoggerException ("Could not open the log file for writing!");
         }
         
         fwrite ($logFile, $message);

         fclose ($logFile);
      }
   }

   public function logInfo ($message, $level = 0)
   {
      $this->log ("(III) [%s] <%s> %s\n",
         date ("%c"), $_SERVER ['QUERY_STRING'], $message);
   }

   public function logError ($message, $level = 0)
   {
      $this->log ("(EEE) [%s] <%s> %s\n",
         date ("%c"), $_SERVER ['QUERY_STRING'], $message);
   }

   public function logFatal ($message, $level = 0)
   {
      $this->log ("(XXX) [%s] <%s> %s\n",
         date ("%c"), $_SERVER ['QUERY_STRING'], $message);
   }
}

?>
