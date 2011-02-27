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

include_once "User.php";
include_once "Course.php";
include_once "Content.php";

class UserClass extends NonUserClass {
   function __construct ()
   {
      parent::__construct ();

      // Load the user's course enrollment information from the database. 
      $this->loadUserInfo ();

      $this->addActions (array (
         'changePassword'           => 'actionChangePassword',
         'logout'                   => 'actionLogout',
         'view'                     => 'actionView',
         'submitPassword'           => 'submitPassword'));

      // The user is already logged in under this class.
      // No need for login processing, remove login functions.
      $this->removeActions (array (
         'login', 'submitLogin'));
         
      // Add menu items for account functions.
      $this->getMenu ()->addItem (
         "Account", new ActionMenu (array (
            "My Courses"         => $this->generateCoursesMenu (),
            "Home"               => new HyperlinkAction ($_SERVER ['PHP_SELF']),
            "Change Password"    => 'changePassword',
            "sep1"               => '---',
            "Logout"             => 'logout'
         ))
      );
   }
   
   /*
    * Fetches the user info and enrollments for the user.
    */ 
   protected function loadUserInfo ()
   {
      // Fetch the user and enrollments for this user. 
      $this->user = User::byUserID ($_SESSION ['userid']);

      $enrollments_courses = $this->user->getCourseEnrollments ();
      $this->enrollments = $enrollments_courses [0];
      $this->courses = $enrollments_courses [1];
   }

   /*
    * Gets the User object associated with the current user.
    * Fetched initially upon loading.
    */
   protected function getUser ()
   {
      return $this->user;
   }

   /*
    * Gets the list of FactCourseEnrollmentVO objects for each 
    * course in which the user is enrolled.
    */
   protected function getCourseEnrollments ()
   {
      return $this->enrollments;
   }

   /*
    * Gets the list of Course objects for each course in which
    * the user is enrolled.
    */
   protected function getCourses ()
   {
      return $this->courses;
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
      new TextEntity ($p, "To change your password, fill out the form below, <br/>then click Change Password.");
      new ChangePasswordForm ($div, '?action=submitPassword');
   }

   protected function actionView ($contentDiv)
   {
      // Get the path of the item to be viewed.  The first name
      // in the path is the code of a course, and the remainder
      // of the names are a path relative to the course-root
      // of said course.  The path is separated by forward slashes.

      if (empty ($_GET ['path'])) {
         throw new CinciException ("View Error", "No path specified.");
      }

      // Convert the path to an array, and remove any empty path
      // names therein.  This allows leniency in leading and trailing
      // slashes, e.g. "/course/folder/" is the same as "course/folder".
      $pathArray = explode ('/', $_GET ['path']);
      $pathArray = array_diff ($pathArray, array (''));

      if (count ($pathArray) < 1) {
         throw new CinciException ("View Error", "No path specified.");
      }

      $courseCode = array_shift ($pathArray);

      $user = User::byUserID ($_SESSION ['userid']);
      $course = Course::byCourseCode ($courseCode);
      $enrollment = $course->getEnrollment ($user);

      if (empty ($course->courseID)) {
         throw new CinciAccessException ("The specified course does not exist.");
      }

      // If the user is not enrolled in the course, and the user does not
      // have '_sysopReadWrite' permissions, deny access.   
      if (empty ($enrollment) and !$this->authorizeCheck ('_sysopReadWrite')) {
         throw new CinciAccessException (
            "You are not authorized to access this course.");
      }

      // If the item doesn't exist, a CinciAccessException will be thrown.
      $entryPoint = CourseContent::byContentID ($course->entryPointID)->resolve ();
      $content = NULL;

      if (count ($pathArray) > 0) {
         $content = $entryPoint->resolvePath ($pathArray, $this, $user, $course, $enrollment);
      } else {
         $content = $entryPoint;
      }

      // Ask the content to display itself as a Div in the given content div.
      $content->display ($contentDiv);
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
         "Click a course in the My Courses menu above to begin.");

      $this->showCoursesList ($div);
   }
   
   /*
    * Prints a list of Div links to the user's enrolled courses.
    */
   protected function showCoursesList ($contentDiv)
   {
      $courses = $this->getCourses ();
      $enrollments = $this->getCourseEnrollments ();
      $roles = enumerateCourseRoles ();

      for ($x = 0; $x < count ($courses); $x++) {
         $link = new Hyperlink ($contentDiv, sprintf (
            "?action=view&path=%s", htmlentities ($courses [$x]->courseCode)));

         $item = new Span ($link, $courses [$x]->courseName, "content-item");
      }
   }

   /*
    * Adds a courses menu to the user's menu bar.
    */
   protected function generateCoursesMenu ()
   {
      $coursesMenu = new ActionMenu (); 
      
      if (count ($this->getCourses () > 0)) { 
         foreach ($this->getCourses () as $course) {
            $coursesMenu->addItem (
               htmlentities ($course->courseName),
               new HyperlinkAction (sprintf ("?action=view&path=%s", htmlentities ($course->courseCode)))
            );
         }
      }

      return $coursesMenu;
   } 
}

?>

