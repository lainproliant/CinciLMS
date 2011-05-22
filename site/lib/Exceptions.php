<?php

include_once "User.php";

/*
 * Exceptions: Types of exceptions in the 
 * Cincinnatus Learning Management System.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License.
 */

// A generic CinciLMS system exception.
class CinciException extends Exception {
   private $header;

   function __construct ($header, $message)
   {
      parent::__construct ($message);
      $this->header = $header;
   }

   public function getHeader ()
   {
      return $this->header;
   }

   public function logException ($logger)
   {
      $logger->logError (sprintf ("[%s] %s: %s",
         User::getCurrentUsername (),
         $this->getHeader (),
         $this->getMessage ()));
   }
}

// An exception relating to database queries and connections.
class CinciDatabaseException extends CinciException {
   private $dberror;

   function __construct ($header, $message, $error = NULL)
   {
      parent::__construct ($header, $message);
      $this->dberror = $error;
   }

   function getDBError ()
   {
      return $this->dberror;
   }

}

// An exception for login errors.
class CinciLoginException extends CinciException { 
   private $username;

   function __construct ($username, $message)
   {
      parent::__construct ("Login Error", $message);
      $this->username = $username;
   }

   public function getUsername ()
   {
      return $this->username;
   }

   public function logException ($logger)
   {
      $logger->logInfo (sprintf ("[%s]->[%s] Login failed.",
         User::getCurrentUsername (),
         $this->getUsername ()));
   }
}

// An exception for access and permissions errors.
class CinciAccessException extends CinciException {
   function __construct ($message)
   {
      parent::__construct ("Access Denied", $message);
   }
}


// An exception for expired sessions.
class ExpiredSessionException extends CinciException {
   function __construct ()
   {
      parent::__construct ("Session Expired", "Your session has expired.  Please login again.");
   }
   
   public function logException ($logger)
   {
      $logger->logInfo (sprintf ("[%s] Session expired.",
         User::getCurrentUsername ()));
   }
}

?>
