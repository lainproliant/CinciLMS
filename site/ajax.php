<?php

/*
 * ajax.php: An AJAX portal into the Cincinnatus Learning Management System.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 *
 * Released under the GNU General Public License, verion 3.
 */

include_once "lib/SiteConfig.php";
include_once "lib/Exceptions.php";
include_once "lib/util/AuthorityClass.php";
include_once "lib/util/XMLEntity.php";
include_once "lib/AJAXEntities.php";

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

   // Generate an AJAX request reply document.
   $ajaxReply = new AJAXReply ();

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

      $actionToAuthorize = 'AJAX_' . $_GET ['action'];

      // Pass the AJAX request on to the authority class to be processed.
      try {
         $SiteLog->logInfo (sprintf (
            "AJAX request: %s", $actionToAuthorize));

         $class->authorize ($actionToAuthorize, $ajaxReply);

      } catch (NotAuthorizedException $e) {
         throw new CinciAccessException ('Unauthorized AJAX request.');
      }
   
   } catch (CinciException $e) {
      generateExceptionReply ($ajaxReply, $e->getHeader (),
         $e->getMessage ());

   } catch (Exception $e) {
      generateExceptionReply ($ajaxReply, 'Unknown Exception',
        $e->getMessage ());

   }

   // Deliver the AJAX request reply (standalone = yes).
   xml_header ("1.0", "UTF-8", "yes");
   $ajaxReply->printString ();
}

/*
 * Generates an AJAX reply for an exception case.
 */
function generateExceptionReply ($ajaxReply, $header, $message)
{
   new AJAXStatus ($ajaxReply, 'exception');
   new AJAXHeader ($ajaxReply, $header);
   new AJAXMessage ($ajaxReply, $message);
}

// Call the main method.
main ();

?>
