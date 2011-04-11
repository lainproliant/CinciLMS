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

/*
 * accessDenied: Prints an access denied message.
 */
function accessDenied ($contentDiv)
{
   $div = new Div ($contentDiv, 'warning prompt');
   $h3 = new XMLEntity ($div, 'h3');
   new TextEntity ($h3, "Unauthorized Action");

   $p = new XMLEntity ($div, 'p');
   new TextEntity ($p, "You are not authorized to perform the specified action.");
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


/*
 * populateMenu: Recursively fills an unordered list with a list of 
 *               action items and submenus.
 *
 * menuList:      The list to be populated.
 * class:         The user's authority class. 
 * userMenu:      The menu to be populated.
 * mainMenu:      Whether the given list of items is the top level
 *                and should be given the 'main_menu' CSS class.
 *                This is FALSE by default.
 */
function populateMenu ($menuList, $class, $userMenu, $level = 1)
{
   foreach ($userMenu->getItemNames () as $name) {
      $menuItem = $userMenu->getItem ($name);

      if (is_object ($menuItem) and get_class ($menuItem) == "ActionMenu") {
         $header = new Para (NULL, $name);
         $listItem = $menuList->addListItem ($header);

         if ($level > 1) {
            new Image ($header, 'images/menu-right.png', 'submenu');
         } else {
            new Image ($header, 'images/menu-down.png', 'submenu');
         }

         $subMenu = new UnorderedList ($listItem);
         $subMenu->setAttribute ('class', sprintf ("L%d", $level));

         if ($menuItem->getCount () > 0) {
            populateMenu ($subMenu, $class, $menuItem, $level + 1);
         } else {
            $para = new Para (NULL, '[empty]', 'disabled');
            $subMenu->addListItem ($para);
         }
      
      } elseif (is_object ($menuItem) and get_class ($menuItem) == "HyperlinkAction") {
         $link = new TextLink (NULL,
            $menuItem->getHyperlink (), $name);
         $menuList->addListItem ($link);

      } elseif (is_string ($menuItem) and $menuItem == "---") {
         $li = new ListItem ($menuList);
         $li->setAttribute ('class', 'separator');

      } elseif (is_string ($menuItem) and $class->authorizeCheck ($menuItem)) {
         $link = new TextLink (NULL,
            sprintf ("?action=%s", $menuItem), $name);
         $menuList->addListItem ($link);
      }
         
   }
}

/*
 * main: Defines the main entry point.
 */
function main ()
{
   global $SiteConfig;

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
         accessDenied ($contentDiv);
      }

   } catch (ExpiredSessionException $e) {
      // The session has expired, notify the user.
      sessionExpired ($contentDiv);

   } catch (CinciLoginException $e) {
      // The login was unsuccessful, notify the user.
      loginFailed ($contentDiv, $e);
      $class->showLogin ($contentDiv);

   } catch (CinciDatabaseException $e) {
      // There was a database error, notify the user.
      databaseError ($contentDiv, $e);

   } catch (CinciException $e) {
      // There was another kind of error.
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
