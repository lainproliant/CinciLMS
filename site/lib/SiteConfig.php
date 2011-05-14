<?php

/*
 * SiteConfig.php: Session validation and configuration.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3.
 */

// LRS-TODO: Include further user classes here.
include_once "NonUserClass.php";
include_once "UserClass.php";
include_once "SysopClass.php";
include_once "AdminClass.php";
include_once "Exceptions.php";
include_once "Logger.php";

define ("SYSTEM_ROLE_USER",   0);
define ("SYSTEM_ROLE_SYSOP",  1);
define ("SYSTEM_ROLE_ADMIN",  2);

// Define a global configuration array.
$SiteConfig = array ();

// Define a global logger.
$SiteLog = NULL;

/*
 * Initialize the global SiteConfig and initialize other
 * globally important settings.
 */
function globalSiteConfig ()
{
   global $SiteConfig;
   global $SiteLog;

   // Load configuration from "config.ini".
   $SiteConfig = parse_ini_file ("config.ini", TRUE);

   // Set the default timezone for time functions.
   date_default_timezone_set ($SiteConfig ['site']['default_timezone']);

   // Initialize the global log file.
   $SiteLog = new Logger ($SiteConfig ['site']['log_file'], $SiteConfig ['site']['log_level']);
}


/*
 * Gets the current username.
 */
function getCurrentUsername ()
{
   if (! isset ($_SESSION ["username"])) {
      return '<guest>';
   } else {
      return $_SESSION ["username"];
   }
}

/*
 * Returns a list of SystemRole IDs and descriptive
 * names for them.
 */
function enumerateSystemRoles ()
{
   return array (
      SYSTEM_ROLE_USER => "User",
      SYSTEM_ROLE_SYSOP => "Sysop",
      SYSTEM_ROLE_ADMIN => "Admin");
}

/*
 * Creates an AuthorityClass object for the given system role.
 *
 * $system_role:     The user's system role.
 *
 * Returns an appropriate AuthorityClass for the given system role.
 */
function createSystemRoleAuthorityClass ($system_role)
{
   // LRS-TODO: Create the proper type of AuthorityClass for the
   // provided system role.  For now, simply return NonUserClass.

   switch ($system_role) {
   case SYSTEM_ROLE_USER:
      return new UserClass ();
      break;

   case SYSTEM_ROLE_SYSOP:
      return new SysopClass ();
      break;

   case SYSTEM_ROLE_ADMIN:
      return new AdminClass ();
      break;

   default:
      throw new CinciException (sprintf ("Invalid system role: %d", $system_role));
   }

   return new NonUserClass ();
}

/*
 * Validates the user session.
 *
 * Returns an appropriate AuthorityClass for the user.
 */
function validateSession ()
{
   global $SiteConfig;

   // Confirm that the user is logged in.
   if (! isset ($_SESSION['username'])) {
      // The user is not logged in.
      return new NonUserClass ();
   }

   // Confirm that the session is valid.
   if (! isset ($_SESSION['timestamp'])) {
      // This is not a valid session anyway.
      // This probably won't happen, but end
      // the session and throw a nonUserClass.
      session_destroy ();
      return new NonUserClass ();
   }

   // Confirm that the session timeout has not passed.
   if (time () - $_SESSION['timestamp'] > $SiteConfig['site']['session_timeout']) {
      // The session has expired.
      session_destroy ();
      throw new ExpiredSessionException ();
   }

   // The session is valid.  Update the session timestamp to prevent timeout.
   $_SESSION['timestamp'] = time ();

   // The session is valid.  Return an appropriate AuthorityClass. 
   return createSystemRoleAuthorityClass ($_SESSION['system_role']);
}
