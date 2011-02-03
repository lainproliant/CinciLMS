<?php

/*
 * UserClass:  Defines the actions and properties of a user in the
 *             Cincinnatus Learning Management System.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "util/XMLEntity.php";
include_once "NonUserClass.php";
include_once "Exceptions.php";

class UserClass extends NonUserClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
         'logout'                   => 'actionLogout',
         'changePassword'           => 'actionChangePassword',
         'submitPassword'           => 'submitPassword'));

      // The user is already logged in under this class.
      // No need for login processing, remove login functions.
      $this->removeActions (array (
         'login', 'submitLogin'));

      // Add menu items for account functions.
      $this->getMenu ()->addItem (
         "Account", new ActionMenu (array (
            "Change Password"    => 'changePassword',
            "Logout"             => 'logout'
         ))
      );
   }

   protected function actionLogout ($contentDiv)
   {
      session_destroy ();
      
      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Logged Out");
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "You have been logged out of the system.  Have a good day!");

      return new NonUserClass ();
   }

   protected function actionChangePassword ($contentDiv)
   {
      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Change Password");
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "To change your password, fill out the form below, then click Change Password.");
      new ChangePasswordForm ($div, '?action=submitPassword');
   }

   protected function submitPassword ($contentDiv)
   {
      global $SiteConfig;

      // If no post data was provided, throw an error.
      if (sizeof ($_POST) == 0) {
         throw new CinciException ("Password Change Error",
            "Please use the Change Password link in the Account menu to change your password.");
      }

      $old_password = $_POST['old_password'];
      $new_password = $_POST['new_password_A'];

      $username = $_SESSION['username'];
      
      // Fetch the user.
      $user = User::byUsername ($username);

      // Check the current password to prevent an unauthorized password change.
      if (! $user->checkPassword ($old_password)) {
         throw new CinciException ("Password Change Error",
            "The old password was incorrect.");
      }

      // The password was correct, attempt to change it.
      // Salting and re-hashing is taken care of by the User class.
      $user->changePassword ($new_password);
      
      // The password was changed successfully!
      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Password Changed");
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Success!  Your password has been changed.");
   }
   
   /*
    * Overridden function from NonUserClass.
    *
    * Shows a welcome message instead of a login form.
    */
   protected function showWelcome ($contentDiv)
   {
      $div = new Div ($contentDiv, 'prompt');

      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf (
         'Welcome, %s!', htmlentities ($_SESSION ['first_name'])));
      $p = new Para ($div,
         "Click a course below to begin.");
   }

}

?>

