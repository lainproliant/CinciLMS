<?php

/*
 * Warnings.php: Helper functions for formatting warning notifications.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3.
 */

/*
 * accessDenied: Prints an access denied message.
 */
function accessDenied ($contentDiv, $e)
{
   $div = new Div ($contentDiv, 'warning prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, "Access Denied");

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, $e->getMessage ());
}

/*
 * databaseError: Notifys the user of a database error.
 */
function databaseError ($contentDiv, $e)
{
   $div = new Div ($contentDiv, 'error prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, $e->getHeader ());

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, $e->getMessage ());
   
   $p = new Para ($div, sprintf ('<code>%s</code>',
      $e->getDBError ()));
}

/*
 * genericError: Prints a message about a generic error.
 */
function genericError ($contentDiv, $e)
{
   $div = new Div ($contentDiv, 'error prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, $e->getHeader ());

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, $e->getMessage ());
   
   $p = new Para ($div, sprintf ('<code>%s</code>',
      get_class ($e)));
}

/*
 * loginFailed: Prints a message about a failed login.
 */
function loginFailed ($contentDiv, $e)
{
   $div = new Div ($contentDiv, 'error prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, "Login Failed");

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, $e->getMessage ());
}

/*
 * sessionExpired: Prints a message nofitying the user that their
 *                 session has expired.
 */
function sessionExpired ($contentDiv)
{
   $div = new Div ($contentDiv, 'warning prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, "Session Expired");

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, "Your session has expired.  Please ");
   new TextLink ($p, "?action=login", "click here");
   new TextEntity ($p, " to login again.");
}


