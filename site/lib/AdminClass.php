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

# LRS-DEBUG: Remove after debug_testData is removed.
include_once "Course.php";
include_once "Content.php";
include_once "Grades.php";

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
         '_adminUnenrollAbility'    => NULL,
         '_adminWriteGradesAbility' => NULL,
         'newUser'                  => 'actionNewUser',
         'editUser'                 => 'actionEditUser',
         'submitNewUser'            => 'submitNewUser',
         'submitUserEdit'           => 'submitUserEdit',
         'debug_testData'           => 'debug_testData'));
      
       
      $createMenu = $this->getMenu ()->getItem ('Create');
      
      if (empty ($createMenu)) {
         $createMenu = new ActionMenu ();
         $this->getMenu ()->addItem ('Create', $createMenu);
      }
      
      $createMenu->addItem (
         "New User", 'newUser');
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

      if (empty ($_POST)) {
         throw new CinciException ("New User Error", "No user information provided.");
      }

      $user = new User ();

      $user->username = $_POST ['username'];
      $user->externalID = $_POST ['externalID'];
      $user->firstName = $_POST ['firstName'];
      $user->middleInitial = $_POST ['middleInitial'];
      $user->lastName = $_POST ['lastName'];
      $user->emailAddress = $_POST ['emailAddress'];
      $user->systemRole = $_POST ['systemRole'];
      $user->isActive = $_POST ['isActive'];

      $random_password = randomPassword ();
      $salted_password = hash ('sha256', $SiteConfig ['db']['static_salt'] . $random_password);
     
      $user->generateSalt ();
      $user->setPassword ($salted_password);

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
      
      if (empty ($_POST)) {
         throw new CinciException ("Edit User Error", "No user information provided.");
      }

      $user = User::byUserID ($_POST ['userID']);

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
   
   # LRS-DEBUG: Generate test users and enrollments.
   protected function debug_testData ($contentDiv)
   {
      global $SiteConfig;

      // Create three test users.
      $userInfo = array (
         'bob'    => array ("Bob", 'I', "Teacher"),
         'alice'  => array ("Alice",'C', "Learner"),
         'eve'    => array ("Eve", 'S', "Dropper"));

      $users = array ();
      $courses = array ();
      
      // Give each of them a user account and their own course.
      foreach ($userInfo as $username => $name) {
         $user = new User ();
         $user->username = $username;
         $user->firstName = $name [0];
         $user->middleInitial = $name [1];
         $user->lastName = $name [2];
         
         $salted_password = hash ('sha256',
            $SiteConfig ['db']['static_salt'] . $username);

         $user->setPassword ($salted_password);

         $user->systemRole = SYSTEM_ROLE_USER;
         $user->isActive = 1;

         $user->insert ();

         $users [] = $user;

         $course = Course::createNewCourse (
            sprintf ("%s's course", $user->firstName),
            sprintf ("lrs_debug_test_%s", $user->username),
            COURSE_DEFAULT_PERMISSIONS,
            $user);

         $course->enrollUser ($user, COURSE_ROLE_INSTRUCTOR);
         $courses [] = $course;
      }

      // Create some test grade columns!
      foreach ($courses as $course) {
         $gradeColumns = array ();

         for ($X = 0; $X < 7; $X++) {
            $gradeColumn = new GradeColumn ();

            $gradeColumn->courseID = $course->courseID;
            $gradeColumn->name = sprintf ("Column %d", $X);
            $gradeColumn->pointsPossible = 100;

            $gradeColumn->insert ();

            $gradeColumns [] = $gradeColumn;
         }
      }
      
      // Enroll each user as a student in each other course.
      foreach ($courses as $course) {
         foreach ($users as $user) {
            if (is_null ($course->getEnrollment ($user))) {
               $course->enrollUser ($user, COURSE_ROLE_STUDENT);
            }
         }
      }
   }
}

?>
