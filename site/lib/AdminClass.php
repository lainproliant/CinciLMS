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
include_once "User.php";

function randomPassword ($length = 6)
{
   $random_password = "";

   $charspace = array_map ("chr", array_merge (
      range (48, 57), range (65, 90), range (97, 122)));

   for ($x = 0; $x < $length; $x ++) {
      $random_password .= $charspace [array_rand ($charspace)];
   }

   return $random_password;
}

class AdminClass extends SysopClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
         'newUser'                  => 'actionNewUser',
         'editUser'                 => 'actionEditUser',
         'submitNewUser'            => 'submitNewUser',
         'submitUserEdit'           => 'submitUserEdit'));

      $this->getMenu ()->addItem (
         "Users", new ActionMenu (array (
            "Create a New User"     => 'newUser',
            "Search Users"          => 'searchUsers')));
   }

   protected function actionNewUser ($contentDiv)
   {
      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Create a New User");
      new Para ($div, "Edit the user's details below, then click Submit.");
      new UserForm ($div, '?action=submitNewUser', $this);
   }

   protected function actionEditUser ($contentDiv)
   {
      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Edit User");
      new Para ($div, "Edit the user's details below, then click Submit.");

      $user = User::byUserID ($_GET ['userID']);
      new UserForm ($div, '?action=submitUserEdit', $this, $user);
   }

   protected function submitNewUser ($contentDiv)
   {
      global $SiteConfig;

      $user = new User ();

      $user->username = $_POST ['username'];
      $user->externalID = $_POST ['externalID'];
      $user->firstName = $_POST ['firstName'];
      $user->middleInitial = $_POST ['middleInitial'];
      $user->lastName = $_POST ['lastName'];
      $user->emailAddress = $_POST ['emailAddress'];
      $user->systemRole = $_POST ['systemRole'];
      $user->isActive = $_POST ['isActive'];
      
      $user->passwordSalt = hash ('sha256', openssl_random_pseudo_bytes (64, $strong_crypto));
      $random_password = randomPassword ();

      $user->passwordHash = hash ('sha256', $user->passwordSalt .
         hash ('sha256', $SiteConfig ['db']['static_salt'] . $random_password));
         
      try {
         $user->insert ();

      } catch (DAOException $e) {
         throw new CinciDatabaseException ("User Creation Error", 
            "There was an error creating the new user.",
            $e->error);
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Success!");
      new Para ($div, sprintf (
         "The user \"%s\" was created successfully.", htmlentities ($user->username)));
      new Para ($div, sprintf (
         "The new user's password is: <code>%s</code>", htmlentities ($random_password)));
   }

   protected function submitUserEdit ($contentDiv)
   {
      global $SiteConfig;

      $user = new User ();
      $user->byUserID ($_POST ['userID']);

      $user->username = $_POST ['username'];
      $user->externalID = $_POST ['externalID'];
      $user->firstName = $_POST ['firstName'];
      $user->middleInitial = $_POST ['middleInitial'];
      $user->lastName = $_POST ['lastName'];
      $user->emailAddress = $_POST ['emailAddress'];
      $user->systemRole = $_POST ['systemRole'];
      $user->isActive = $_POST ['isActive'];
      
      try {
         $user->save ();

      } catch (DAOException $e) {
         throw new CinciDatabaseException ("User Edit Error", 
            "There was an error editing the user.",
            $e->error);
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Success!");
      new Para ($div, sprintf (
         "The user \"%s\" was edited successfully.", htmlentities ($user->username)));
   }
}
