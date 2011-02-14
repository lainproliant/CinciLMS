<?php

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

   function getHeader ()
   {
      return $this->header;
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
   function __construct ($message)
   {
      parent::__construct ("Login Error", $message);
   }
}

?>
