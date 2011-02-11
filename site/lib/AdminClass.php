<?php

/*
 * AdminClass: Defines the actions and properties of a admin in the
 *             Cincinnatus Learning Management System.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */


include_once "util/XMLEntity.php";
include_once "SysopClass.php";
include_once "Exceptions.php";

include_once "UserForm.php";

// LRS-TODO: implement
class AdminClass extends SysopClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
         'newUser'                  => 'actionNewUser',
         'searchUsers'              => 'actionSearchUsers',
         'editUser'                 => 'actionEditUser',
         'newCourse'                => 'actionNewCourse',
         'courseProperties'         => 'courseProperties',
         'deleteCourse'             => 'deleteCourse'));

      $this->getMenu ()->addItem (
         "Users", new ActionMenu (array (
            "Create a New User"     => 'newUser',
            "Search Users"          => 'searchUsers')));
   }

   protected function actionNewUser ($contentDiv)
   {
      $div = new Div ($contentDiv, "login prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Create a New User");
      new Para ($div, "Edit the user's details below, then click Submit.");
      new UserForm ($div, '?action=submitNewUser', $this);
   }
}
