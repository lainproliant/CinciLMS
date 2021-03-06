<?php

/*
 * NonUserClass: Defines the actions and properties of a non-user
 * or guest in the Cincinnatus Learning Management System
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "util/AuthorityClass.php";
include_once "util/XMLEntity.php";
include_once "Exceptions.php";
include_once "Database.php";
include_once "LoginForm.php";

include_once "User.php";

class NonUserClass extends AuthorityClass {
   function __construct ()
   {
      parent::__construct ();

      $actionMethods = array (
         'login'           => 'showLogin',
         'welcome'         => 'showWelcome',
         'submitLogin'     => 'submitLogin');

      $this->addActions ($actionMethods);
      
      $this->getMenu ()->addItem ("Login", 'login');
   }
   
   public function showLogin ($contentDiv)
   {
      $div = new Div ($contentDiv, "login prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Login");
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Please login using the form below.");
      new LoginForm ($div, '?action=submitLogin');
   }
   
   protected function submitLogin ($contentDiv)
   {
      global $SiteConfig;
      global $SiteLog;

      // Sleep for a few seconds to help mitigate brute force attacks.
      sleep ($SiteConfig ['site']['login_delay']);

      // If no post data was provided, simply show a login form. 
      if (sizeof ($_POST) == 0) {
         if (! isset ($_SESSION ['username'])) {
            $this->showLogin ($contentDiv);
            return;
         }
      }

      $username = $_POST['username'];
      $password = $_POST['password'];
      
      // Attempt to retrieve the user salt for the specified user.
      $user = User::byUsername ($username);
      
      if (empty ($user)) {
         throw new CinciLoginException ($username,
            "The username and password you provided were incorrect.");
      }

      // Do the passwords match?
      if (! $user->checkPassword ($password)) {
         // The password was incorrect.
         throw new CinciLoginException ($username,
            "The username and password you provided were incorrect.");
      }

      // We must confirm that the user is still active, otherwise login should fail.
      if (! $user->isActive) {
         throw new CinciLoginException ($username, 
            "This account has been disabled because the user is not active.  Please contact a system administrator.");
      }

      // Login was successful!  Create a new session. 
      session_destroy ();
      session_start ();
      $_SESSION['username'] = $user->username;
      $_SESSION['userid'] = $user->userID;
      $_SESSION['timestamp'] = time ();
      $_SESSION['system_role'] = $user->systemRole;
      $_SESSION['first_name'] = $user->firstName;
      $_SESSION['last_name'] = $user->lastName;

      // Write the login even to the log file.
      $SiteLog->logInfo (sprintf ("[%s] Logged in.",
         User::getCurrentUsername ()));

      // LRS-TODO: This needs to be replaced with a delay page that occurs before login.
      // Trigger an HTTP refresh after 2 seconds.
      header ('Refresh: 1; url=' . $_SERVER['PHP_SELF']);
      
      $div = new Div ($contentDiv, "prompt"); 
      $h3 = new XMLEntity ($div, 'h3');
      new TextEntity ($h3, "Logging in...");
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Logging in, please wait...");
      $p = new XMLEntity ($div, 'p');
      $p->setAttribute ('style', 'text-align: center;');
      new Image ($p, 'images/redirect.gif', 'redirecting...');
   }

   protected function showWelcome ($contentDiv)
   {
      $this->showLogin ($contentDiv);
   }
}

?>
