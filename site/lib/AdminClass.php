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

}
