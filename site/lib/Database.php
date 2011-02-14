<?php

/*
 * Database: Defines the method for connecting to the Cinci LMS database.
 * (c) 2011 Lee Supe 
 * Releaseed under the GNU General Public License, version 3.
 */

include_once "Exceptions.php";

/*
 * Creates a connection to the Cinci LMS database.
 *
 * Returns a new MySQLi object representing the database connection.
 */
function databaseConnect ()
{
   global $SiteConfig;

   // Attempt to connect to the database.
   $hostname = $SiteConfig['db']['hostname'];

   if (array_key_exists ('port', $SiteConfig['db']) and $SiteConfig['db']['port'] != '') {
      $hostname .= ':' . $SiteConfig['db']['port'];
   }

   $db = new mysqli (
      $hostname,
      $SiteConfig['db']['username'],
      $SiteConfig['db']['password'],
      $SiteConfig['db']['database']);
   
   if (mysqli_connect_errno ()) {
      // Translate the error into a CinciDatabaseException.
      throw new CinciDatabaseException ("Database Connection Error!",
         "There was an error connecting to the database.",
         mysqli_connect_error ());
   }

   // Upon success, return a handle to the database.
   return $db;
}

?>
