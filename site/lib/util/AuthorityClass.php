<?php

/*
 * AuthorityClass: Defines a set of authorized actions based
 * upon a finite hierarchy of user classes.
 *
 * (c) 2010, 2011 Lee Supe 
 * Released under the GNU General Public License, version 3.
 */

class NotAuthorizedException extends Exception { }

/*
 * AuthorityClass: Defines a user class.
 */
class AuthorityClass {
   private $actionMethods;
   private $menu;
   private $contextMenu;

   /*
    * Constructs an abstract authority class.
    * This is called by child classes to initialize the
    * action methods and groups arrays.
    */
   function __construct ()
   {
      $this->actionMethods = array ();
      $this->menu = new ActionMenu ();
      $this->contextMenu = new ActionMenu ();
   }
   
   /*
    * Authorize the requested action.  Either performs
    * the given action, passing the specified parameter,
    * or raises a NotAuthorizedException if the specified
    * action is unavailable to this particular class.
    */
   public function authorize ($action, $param)
   {
      if (array_key_exists ($action, $this->actionMethods) and 
         ! empty ($this->actionMethods[$action])) {
         return $this->{$this->actionMethods[$action]} ($param);
      } else {
         throw new NotAuthorizedException ();
      }
   }

   /*
    * Check if a particular action is authorized by this user role.
    */
   public function authorizeCheck ($action)
   {
      if (array_key_exists ($action, $this->actionMethods)) {
         return true;
      } else {
         return false;
      }
   }

   /*
    * Returns a reference to the user's action menu.
    */
   public function getMenu ()
   {
      return $this->menu;
   }

   /*
    * Returns a reference to a special context menu which can
    * be used to list context-specific menu options.
    */
   public function getContextMenu ()
   {
      return $this->contextMenu;
   }

   /*
    * Adds a set of actions to the action methods.  Overrides
    * any methods which were defined before, e.g. by superclasses
    * to the current authority class.
    */
   protected function addActions ($actionMethods)
   {
      foreach ($actionMethods as $action => $method) {
         $this->actionMethods [$action] = $method;
      }
   }
   
   /*
    * Removes a set of actions from the list of authorized actions.
    * This may be useful for user classes where actions of the
    * superclass are not appropriate for the subclass, e.g. the
    * 'login' action for non-users is not appropriate for users
    * who are already logged in.
    */
   protected function removeActions ($actionsToRemove)
   {
      foreach ($actionsToRemove as $action) {
         unset ($this->actionMethods [$action]);
      }
   }

   /*
    * LRS-DEBUG: Get the internal actionGroups array.
    */
   public function debug_getActionGroups ()
   {
      return $this->actionGroups;
   }
}

/*
 * Defines a menu for action groups.  This is used to construct
 * the menu in the navigation pane from the available actions.
 */
class ActionMenu {
   private $children;

   function __construct ($items = NULL)
   {
      $this->children = array ();

      if (! empty ($items)) {
         $this->addItems ($items);
      } 
   }

   /*
    * Adds an item to the action menu.  If the item is already
    * defined, it will be replaced.
    */
   public function addItem ($name, $item)
   {
      $this->children [$name] = $item;
      return $this->getItem ($name);
   }

   /*
    * Adds multiple items to the list using an associative array.
    */
   public function addItems ($items)
   {
      foreach ($items as $name => $item) {
         $this->addItem ($name, $item);
      }
   }

   /*
    * Retrieves an item from the menu.
    */
   public function getItem ($name)
   {
      if ($this->hasItem ($name)) {
         return $this->children [$name];
      } else {
         return NULL;
      }
   }

   /*
    * Determine whether a submenu item exists.
    */
   public function hasItem ($name)
   {
      if (array_key_exists ($name, $this->children)) {
         return TRUE;
      } else {
         return FALSE;
      }
   }

   /*
    * Returns a list of all item names in this menu.
    */
   public function getItemNames ()
   {
      return array_keys ($this->children);
   }

   /*
    * Returns the number of menu items in this menu.
    */
   public function getCount ()
   {
      return count ($this->children);
   }
}

class HyperlinkAction {
   private $hyperlink;

   function __construct ($hyperlink)
   {
      $this->hyperlink = $hyperlink;
   }

   public function getHyperlink ()
   {
      return $this->hyperlink;
   }
}

?>
