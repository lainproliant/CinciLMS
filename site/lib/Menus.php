<?php

/*
 * Menus.php: Helper functions for menu construction.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3.
 */

/*
 * populateMenu: Recursively fills an unordered list with a list of 
 *               action items and submenus.
 *
 * menuList:      The list to be populated.
 * class:         The user's authority class. 
 * userMenu:      The menu to be populated.
 * level:         The initial level of the items to be added to the list.
 *                Default is 1.
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

      } elseif (is_object ($menuItem) and get_class ($menuItem) == "JavascriptAction") {
         $link = new TextLink (NULL,
            "javascript:void(0);", $name);
         $link->setAttribute ('onclick', $menuItem->getJavascript ());
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


