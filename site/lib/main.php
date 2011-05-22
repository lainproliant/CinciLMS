<?php

/*
 * main.php: Main entry point for the Cincinnatus Learning Management System
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details. 
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once "util/XMLEntity.php";
include_once "Exceptions.php";
include_once "LoginForm.php";
include_once "SiteConfig.php";
include_once "User.php";
include_once "Menus.php";
include_once "Warnings.php";

/*
 * main: Defines the main entry point.
 */
function main ()
{
   global $SiteConfig;
   global $SiteLog;

   // Start a new session or resume the current session.
   session_start ();
   
   // Initialize the global settings array from the config file
   // and other globally important settings.
   globalSiteConfig ();

   // Build the main page structure.
   $containerDiv = new Div (NULL, 'container');
   $naviDiv = new Div ($containerDiv, 'navi');
   $contentDiv = new Div ($containerDiv, 'content');
   
   // A variable for the user class.
   $class = new NonUserClass ();

   // Validate the session and get an authority class.
   try {
      $class = validateSession ();
    
      // If no action is specified, display a welcome page.
      $actionToAuthorize = 'welcome';
      if (isset ($_GET ['action'])) {
         $actionToAuthorize = $_GET['action'];
      }

      try {
         // The actions should return NULL on success, or a new user class.
         $newClass = $class->authorize ($actionToAuthorize, $contentDiv);
         if (! empty ($newClass)) {
            $class = $newClass;
         }
      } catch (NotAuthorizedException $e) {
         throw new CinciAccessException ("You are not authorized to perform the specified action.");
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
   
   $menuDiv = new Div ($naviDiv, 'menu');
   $menuList = new UnorderedList ($menuDiv);
   $menuList->setAttribute ('class', 'L0');
   $menuDiv = new Div ($naviDiv, 'menu');
   $contextMenuList = new UnorderedList ($menuDiv);

   // Populate the navigation div with a functions menu.
   populateMenu ($menuList, $class, $class->getMenu (), TRUE);
   populateMenu ($contextMenuList, $class, $class->getContextMenu ());
   
   $containerDiv->setPrettyPrint (
      $SiteConfig ['site']['pretty'],
      $SiteConfig ['site']['initial_il']);

   $containerDiv->printString ();
}

// Call the main method.
main ();

?>
