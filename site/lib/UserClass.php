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

include_once "ContentForm.php";

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
         'newContent'               => 'actionNewContent',
         'editContent'              => 'actionEditContent',
         'enrollUser'               => 'actionEnrollUser',
         'editEnrollment'           => 'actionEditEnrollment',
         'submitPassword'           => 'submitPassword',
         'submitContent'            => 'submitContent',
         'submitContentEdit'        => 'submitContentEdit'));

      // The user is already logged in under this class.
      // No need for login processing, remove login functions.
      $this->removeActions (array (
         'login', 'submitLogin'));
         
      // Add menu items for account functions.
      $this->getMenu ()->addItem (
         "Account", new ActionMenu (array (
            "Home"               => new HyperlinkAction ($_SERVER ['PHP_SELF']),
            "Change Password"    => 'changePassword',
            "sep1"               => '---',
            "Logout"             => 'logout'
         ))
      );

      $this->getMenu ()->addItem (
            "My Courses", $this->generateMyCoursesMenu ());
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
      $div = new Div ($contentDiv);
      $courseNameHeader = new XMLEntity ($div, 'h3');
      $breadcrumbHeader = new XMLEntity ($div, 'h4');
      $breadcrumbHeader->setAttribute ('class', 'breadcrumb');

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

      // Build a breadcrumb trail in the title path.
      for ($x = 0; $x < count ($pathArray); $x++) {
         $subPathArray = array_slice ($pathArray, 0, $x + 1);

         if ($x + 1 < count ($pathArray)) {
            $subPath = implode ('/', $subPathArray);
            $subPathHref = htmlentities (
               sprintf ('?action=view&path=%s', $subPath), ENT_QUOTES);
            $pathName = htmlentities ($pathArray [$x], ENT_QUOTES);
            
            new TextLink ($breadcrumbHeader, $subPathHref, $pathName);
            new TextEntity ($breadcrumbHeader, ' / ');

         } else {
            new TextEntity ($breadcrumbHeader, htmlentities ($pathArray [$x]), ENT_QUOTES);
         }

      }
      
      $courseCode = $pathArray [0];

      $user = User::byUserID ($_SESSION ['userid']);
      $course = Course::byCourseCode ($courseCode);
      
      // Show the course name in the header.
      new TextEntity ($courseNameHeader, htmlentities ($course->courseName));

      if (empty ($course->courseID)) {
         throw new CinciAccessException ("The specified course does not exist.");
      }
      
      // Display the course's contents in the contentDiv.
      $course->display ($div, $this, $user, $pathArray);
   }

   protected function actionNewContent ($contentDiv)
   {
      $contentType = $_GET ['contentType'];
      $parentPath = $_GET ['parent'];
      
      // Confirm that the parent path exists and that we have the rights
      // to write to it.
      $parentPathArray = explode ('/', $parentPath);
      $parentPathArray = array_diff ($parentPathArray, array (''));
      
      $user = User::byUserID ($_SESSION ['userid']);
      $parentInfo = Course::getPath ($this, $user, $parentPathArray);
      $parent = $parentInfo [0];
      $course = $parentInfo [1];
      $enrollment = $parentInfo [2];

      if (! $parent->checkWriteAccess ($this, $user, $course, $enrollment)) {
         throw new CinciAccessException ("You do not have permission to create content items on this path.");
      }
      
      $absolutePath = implode ('/', $parentPathArray);
      $parent->pathName = $absolutePath;
      
      switch ($contentType) {
      case 'folder':
         $div = new Div ($contentDiv, 'prompt');
         $header = new XMLEntity ($div, 'h3');
         new TextEntity ($header, "Create a New Folder");
         $p = new XMLEntity ($div, 'p');
         new TextEntity ($p, "Enter the folder info below, then click Submit.");

         new FolderForm ($div, '?action=submitContent', $this, $parent);
         break;

      case 'item':
         $div = new Div ($contentDiv, 'prompt');
         $header = new XMLEntity ($div, 'h3');
         new TextEntity ($header, "Create a New Content Item");
         $p = new XMLEntity ($div, 'p');
         new TextEntity ($p, "Enter the content item info below, then click Submit.");

         new ItemForm ($div, '?action=submitContent', $this, $parent);
         break;


      default:
         throw new CinciException ('Content Creation Error', 'Unknown content type specified.');
      }
   }

   protected function actionEnrollUser ($contentDiv)
   {
      $course = NULL;

      if (! empty ($_GET ['courseCode'])) {
         $courseCode = $_GET ['courseCode'];

         $course = Course::byCourseCode ($courseCode);

         if (empty ($course->courseID)) {
            throw new CinciAccessException ("The specified course does not exist.");
         }
         
         $user = $this->getUser ();
         $enrollment = $course->getEnrollment ($user);

         if (! $course->checkEnrollAbility ($this, $user, $enrollment)) {
            throw new CinciAccessException ("You do not have permission to enroll users in this course.");
         }
      }

      $div = new Div ($contentDiv, "prompt"); 
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Enroll a User");
      new Para ($div, "Enter the username and course role below, then click Submit.");
   }

   protected function submitContent ($contentDiv)
   {
      $contentType = $_POST ['contentType'];
      $parentPath = $_POST ['parent'];
      
      // Confirm that the parent path exists and that we have the rights
      // to write to it.
      $parentPathArray = explode ('/', $parentPath);
      $parentPathArray = array_diff ($parentPathArray, array (''));
      
      $user = User::byUserID ($_SESSION ['userid']);
      $parentInfo = Course::getPath ($this, $user, $parentPathArray);
      $parent = $parentInfo [0];
      $course = $parentInfo [1];
      $enrollment = $parentInfo [2];

      if (! $parent->checkWriteAccess ($this, $user, $course, $enrollment)) {
         throw new CinciAccessException ("You do not have permission to create content items on this path.");
      }
      
      $absolutePath = implode ('/', $parentPathArray);
      $parent->pathName = $absolutePath;

      $div = new Div ($contentDiv, 'prompt');

      switch ($contentType) {
      case 'folder':
         $folderName = $_POST ['folderName'];
         $folderPath = $_POST ['folderPath'];
         $accessFlags = implode (',', $_POST ['accessFlags']);

         if (empty ($folderPath)) {
            $folderPath = anumfilter ($folderName);
         }

         $folder = new ContentFolder ();
         $folder->name = $folderName;
         $folder->ownerID = $user->userID;
         $folder->accessFlags = $accessFlags;
         
         try {
            $folder->insert ();
         
         } catch (DAOException $e) {
            throw new CinciDatabaseException ("Content Creation Error", 
               "There was an error creating the new folder.",
               $e->error);
         }
         
         try {
            $parent->addContent ($folder, $folderPath);

         } catch (DAOException $e) {
            $folder->delete ();

            throw new CinciDatabaseException ("Content Insertion Error", 
               "There was an error adding the folder to its parent.",
               $e->error);
         
         }

         $header = new XMLEntity ($div, 'h3');
         new TextEntity ($header, "Success!");
         $p = new XMLEntity ($div, 'p');
         new TextEntity ($p, "The folder was created successfully!");
         break;

      case 'item':
         $itemName = $_POST ['itemName'];
         $itemPath = $_POST ['itemPath'];

         if (empty ($itemPath)) {
            $itemPath = anumfilter ($itemName);
         }

         $itemText = $_POST ['text'];
         $accessFlags = implode (',', $_POST ['accessFlags']);

         $item = new ContentItem ();
         $item->name = $itemName;
         $item->title = $itemName;
         $item->text = $itemText;
         $item->ownerID = $user->userID;
         $item->accessFlags = $accessFlags;
         
         try {
            $item->insert ();

         } catch (DAOException $e) {
            throw new CinciDatabaseException ("Content Creation Error", 
               "There was an error creating the new content item.",
               $e->error);
         }

         try {
            $parent->addContent ($item, $itemPath);

         } catch (DAOException $e) {
            throw new CinciDatabaseException ("Content Insertion Error", 
               "There was an error adding the content item to its parent.",
               $e->error);
         }

         $header = new XMLEntity ($div, 'h3');
         new TextEntity ($header, "Success!");
         $p = new XMLEntity ($div, 'p');
         new TextEntity ($p, "The content item was created successfully!");
         break;

      default:
         throw new CinciException ('Content Creation Error', 'Unknown content type specified.');
      }

      $p = new XMLEntity ($div, 'p');
      new TextLink ($p, sprintf ("?action=view&path=%s", $parent->pathName),
         sprintf ("Return to %s (%s)", htmlentities ($parent->name), htmlentities ($course->courseName)));
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
      $div = new Div ($contentDiv);

      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf (
         'Welcome, %s!', htmlentities ($_SESSION ['first_name'])));
      $p = new Para ($div,
         "Click on a course below to begin.");

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
         $link->setAttribute ('class', 'content-item course');

         new Span ($link, htmlentities ($courses [$x]->courseName), 'title');
      }
   }

   /*
    * Adds a My Courses menu to the user's menu bar.
    */
   protected function generateMyCoursesMenu ()
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

