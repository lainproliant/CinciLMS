<?php

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";

/*
 * DisplayList: Presents a list of items with icons, used to present
 *              menu or other contextual list.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

class DisplayList extends UnorderedList {
   function __construct ($parent, $class = NULL)
   {
      if (! empty ($class)) {
         $class = implode (' ', 'display_list', $class);
      } else {
         $class = 'display_list';
      }

      parent::__construct ($parent, $class);
   }
   
   /*
    * Adds an item to the display list.  Creates the appropriate
    * markup for the item to be displayed in the list with the given
    * text, icon, and an optional link and status icons.
    */
   public function displayItem ($text, $icon, $link = NULL, $statusIcons = array ())
   {
      $itemElement = NULL;

      if (empty ($link)) {
         $itemElement = new Para (NULL, $text);
      } else {
         $itemElement = new TextLink (NULL, $link, $name);
      }

      new Image ($itemElement, $icon, 'display_list_icon');

      foreach ($statusIcons as $statusIcon) {
         new Image ($itemElement, $statusIcon, 'display_list_status_icon');
      }
   }
}

?>
