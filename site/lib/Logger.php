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
   const LEVEL_CRITICAL = 99;
   const LEVEL_INFO = 3;
   const LEVEL_DIAG = 2;
   const LEVEL_DEBUG = 1;

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

   public function log ($message, $level = LEVEL_INFO)
   {
      if ($level <= $this->logLevel) {
         $logFile = fopen ($this->logFileName, "a");
         
         if ($logFile == FALSE) {
            throw new LoggerException ("Could not open the log file for writing!");
         }
         
         fwrite ($logFile, $message);

         fclose ($logFile);
      }
   }
   
   public function logDebug ($message, $level = LEVEL_DEBUG)
   {
      $this->log (sprintf ("(DBG) [%s] <%s> %s\n",
         date ("c"), $_SERVER ['QUERY_STRING'], $message, $level));
   }

   public function logInfo ($message, $level = LEVEL_INFO)
   {
      $this->log (sprintf ("(IFO) [%s] <%s> %s\n",
         date ("c"), $_SERVER ['QUERY_STRING'], $message, $level));
   }

   public function logError ($message, $level = LEVEL_CRITICAL)
   {
      $this->log (sprintf ("(ERR) [%s] <%s> %s\n",
         date ("c"), $_SERVER ['QUERY_STRING'], $message, $level));
   }

   public function logFatal ($message, $level = LEVEL_CRITICAL)
   {
      $this->log (sprintf ("(DIE) [%s] <%s> %s\n",
         date ("c"), $_SERVER ['QUERY_STRING'], $message, $level));
   }
}

?>
