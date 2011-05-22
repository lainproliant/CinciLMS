<?php

/*
 * contentLoad.php: An AJAX portal for fetching the content output
 *    from normal user actions.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 *
 * Released under the GNU General Public License, verion 3.
 */

include_once "lib/util/AuthorityClass.php";
include_once "lib/util/XMLEntity.php";
include_once "lib/SiteConfig.php";
include_once "lib/Exceptions.php";
include_once "lib/Warnings.php";

/*
 * main: The main entry point of the AJAX portal.
 */
function main ()
{
   global $SiteConfig;
   global $SiteLog;

   // Start a new session or resume the current one.
   session_start ();
   
   // Perform global initialization.
   globalSiteConfig ();
   
   // Create the content div.
   $contentDiv = new Div (NULL, 'content');

   // Validate the session and get an authority class.
   $class = new NonUserClass ();

   try {
      $class = validateSession ();

      // If no action is specified, fail.
      if (! isset ($_GET ['action'])) {
         // No action was specified, fail.
         throw new CinciException ("Invalid AJAX Request",
            "No action was specified during the request.");
      }

      $actionToAuthorize = $_GET ['action'];

      // Pass the AJAX content request on to the authority class to be processed.
      try {
         $SiteLog->logInfo (sprintf (
            "AJAX content request: %s", $actionToAuthorize));

         $class->authorize ($actionToAuthorize, $contentDiv);

      } catch (NotAuthorizedException $e) {
         throw new CinciAccessException ('Unauthorized AJAX content request.');
      }

   } catch (ExpiredSessionException $e) {
      // The session has expired, notify the user.
      $e->logException ($SiteLog);
      sessionExpired ($contentDiv);

   } catch (CinciAccessException $e) {
      $e->logException ($SiteLog);
      accessDenied ($contentDiv, $e);

   } catch (CinciLoginException $e) {
      // The login was unsuccessful, notify the user.
      $e->logException ($SiteLog);
      loginFailed ($contentDiv, $e);
      $class->showLogin ($contentDiv);

   } catch (CinciDatabaseException $e) {
      // There was a database error, notify the user.
      $e->logException ($SiteLog);
      databaseError ($contentDiv, $e);

   } catch (CinciException $e) {
      // There was another kind of error.
      $e->logException ($SiteLog);
      genericError ($contentDiv, $e);
   }

   // Deliver the AJAX request reply (standalone = yes).
   xml_header ("1.0", "UTF-8", "yes");
   $contentDiv->printString ();
}

// Call the main method.
main ();

?>
