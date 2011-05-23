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
include_once "GradeForm.php";

/*
 * A method to confirm that the given text is a valid grade expression.
 */
function isValidGrade ($grade)
{
   return preg_match ('/^-?[0-9]+(\.[0-9][0-9]?)?$/', $grade);
}

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
         'gradeCourse'              => 'actionGradeCourse',
         'submitContent'            => 'submitContent',
         'submitEnrollment'         => 'submitEnrollment',
         'submitPassword'           => 'submitPassword',

         'confirmDeleteColumn'      => 'actionConfirmDeleteColumn',
         'deleteColumn'             => 'actionDeleteColumn',
         'newColumn'                => 'actionNewColumn',
         'editColumn'               => 'actionEditColumn',
         'submitNewColumn'          => 'submitNewColumn',
         'submitColumnEdit'         => 'submitColumnEdit',

         'AJAX_saveGrade'           => 'AJAX_saveGrade',
         'AJAX_submitContentSortOrder' => 'AJAX_submitContentSortOrder'
      ));

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
      
      if (! empty ($this->user)) {
         $enrollments_courses = $this->user->getCourseEnrollments ();
         $this->enrollments = $enrollments_courses [0];
         $this->courses = $enrollments_courses [1];
      } else {
         $this->enrollments = array ();
         $this->courses = array ();
      }
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
      global $SiteLog;

      $SiteLog->logInfo (sprintf ("[%s] Logged out.",
         User::getCurrentUsername ()));

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

      // Add the content init javascript and its dependencies.
      new Script ($contentDiv, 'lib/util/js/jquery-ui.min.js');
      new Script ($contentDiv, 'lib/util/js/facebox.js');
      new Script ($contentDiv, 'lib/facebox-init.js');
      new Script ($contentDiv, 'lib/content.js');

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

         // Allow the course to add its context menu.
         $course->addContext ($this, $user, $enrollment);

         if (! $course->checkEnrollAbility ($this, $user, $enrollment)) {
            throw new CinciAccessException ("You are not authorized to enroll users in this course.");
         }
      } else {
         // A course code was not provided.
         throw new CinciException ("Enroll User Error", "No course code was provided.");
      }

      $div = new Div ($contentDiv, "prompt"); 
      $header = new XMLEntity ($div, 'h3');
      $headerText = sprintf ("Enroll a user in %s", htmlentities ($course->courseName));

      new TextEntity ($header, $headerText);
      new Para ($div, "Enter the username and course role below, then click Submit.");

      new UserEnrollmentForm ($div, '?action=submitEnrollment', $course);
   }

   protected function actionGradeCourse ($contentDiv)
   {
      $course = NULL;

      if (! empty ($_GET ['courseCode'])) {
         $courseCode = $_GET ['courseCode'];

         $course = Course::byCourseCode ($courseCode);

         if (empty ($course)) {
            throw new CinciAccessException ("The specified course does not exist.");
         }

         $user = $this->getUser ();
         $enrollment = $course->getEnrollment ($user);

         if (! $course->checkReadGradesAbility ($this, $user, $enrollment)) {
            throw new CinciAccessException ("You are not authorized to read the grade record for this course.");
         }

      } else {
         // A course code was not provided.
         throw new CinciException ("Grade Course Error", "No course code was provided.");
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      $headerText = sprintf ("Grade Record: %s", htmlentities ($course->courseName));

      new TextEntity ($header, $headerText);

      new GradeRecordForm ($contentDiv, $course, $this);
   }

   protected function submitContent ($contentDiv)
   {
      if (empty ($_POST ['contentType']) or empty ($_POST ['parent'])) {
         throw new CinciException ("Content Submission Error", "Content type or parent not specified.");
      }

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
            
            $folder->sortOrder = $folder->contentID;
            $folder->save (false);

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

            $item->sortOrder = $item->contentID;
            $item->save (false);

         } catch (DAOException $e) {
            global $SiteLog;

            $SiteLog->logInfo (sprintf ("LRS-DEBUG: Content Creation Error: %s",
               $e->getMessage ()));
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

      header ('Refresh: 1; url=' . sprintf ("?action=view&path=%s",
         htmlentities ($parent->pathName)));

      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Please wait...");
      $p = new XMLEntity ($div, 'p');
      $p->setAttribute ('style', 'text-align: center;');
      new Image ($p, 'images/redirect.gif', 'redirecting...');
   }

   protected function submitEnrollment ($contentDiv)
   {
      if (empty ($_POST)) {
         throw new CinciException ("Enrollment Error", "No enrollment information provided.");
      }

      $course = Course::byCourseCode ($_POST ['courseCode']);

      if (empty ($course->courseID)) {
         throw new CinciException ("Enrollment Error", "The specified course does not exist.");
      }

      $user = User::byUsername ($_POST ['username']);

      if (empty ($user->userID)) {
         throw new CinciException ("Enrollment Error", "The specified user does not exist.");
      }

      if ($course->getEnrollment ($user) != NULL) {
         throw new CinciException ("Enrollment Error", htmlentities (
            sprintf ("The user \"%s\" is already enrolled in %s.",
            $user->username, $course->courseName)));
      }

      $actingUser = $this->getUser ();
      $actingUserEnrollment = $course->getEnrollment ($actingUser);

      if (! $course->checkEnrollAbility ($this, $actingUser, $actingUserEnrollment)) {
         throw new CinciAccessException ("You are not authorized to enroll users in this course.");
      }

      $courseRoleID = $_POST ['courseRole'];

      // Try to the course enrollment.
      try {
         $course->enrollUser ($user, $courseRoleID);

      } catch (DAOException $e) {
         throw new CinciDatabaseException ("Enrollment Error",
            htmlentities (sprintf ("There was an error enrolling \"%s\" in \"%s\".",
            $user->username, $course->courseCode)),
            $e->error);
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Success!");
      new Para ($div, htmlentities (sprintf ("The user \"%s\" was enrolled in %s successfully.",
         $user->username, $course->courseName)));
      $p = new XMLEntity ($div, 'p');
      new TextLink ($p, sprintf ("?action=view&path=%s", htmlentities ($course->courseCode)),
         sprintf ("Return to %s", htmlentities ($course->courseName)));
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
    * Ask the user if it is okay to delete the given column.
    *
    * This action should be initiated from within a facebox.
    */
   protected function actionConfirmDeleteColumn ($contentDiv)
   {
      $column = NULL;

      if (empty ($_GET ['columnIdentity'])) {
         throw new CinciException ("Delete Column Error",
            "No column identity provided.");
      }
      
      $columnIdentity = $_GET ['columnIdentity'];

      list ($courseID, $columnID) = explode (':', $columnIdentity);

      $course = Course::byCourseID ($courseID);

      if (empty ($course)) {
         throw new CinciException ("Delete Column Error",
            "The specified course does not exist.");
      }

      $enrollment = $course->getEnrollment ($this->getUser ());

      if ($course->checkWriteGradesAbility ($this, $this->getUser (), $enrollment)) {
         $column = GradeColumn::byColumnID ($columnID);

         if (empty ($column)) {
            throw new CinciException ("Delete Column Error",
               "The specified grade column does not exist.");
         }

      } else {
         throw new CinciException ("Delete Column Error",
            "You do not have permission to make changes to the grade record of this course.");
      }
      
      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf ("Delete Column: %s", $column->name));

      new Para ($div, "Are you sure you wish to delete this column?");

      $buttonDiv = new Div ($div, 'buttonRow'); 
      $yesButton = new actionButton ($buttonDiv,
         "Yes",
         sprintf ("window.location.href=\"?action=deleteColumn&columnIdentity=%s\"", 
         $columnIdentity));

      new TextEntity ($buttonDiv, '&nbsp;');

      $yesButton->setAttribute ('class', 'yesButton');

      $noButton = new ActionButton ($buttonDiv,
         "No",
         "javascript:$.facebox.close()");
      $noButton->setAttribute ('class', 'noButton');
   }

   /*
    * Delete the specified grade column.
    */
   protected function actionDeleteColumn ($contentDiv)
   {
      if (empty ($_GET ['columnIdentity'])) {
         throw new CinciException ("Delete Column Error",
            "No column identity provided.");
      }
      
      $columnIdentity = $_GET ['columnIdentity'];

      list ($courseID, $columnID) = explode (':', $columnIdentity);

      $course = Course::byCourseID ($courseID);

      if (empty ($course)) {
         throw new CinciException ("Delete Column Error",
            "The specified course does not exist.");
      }

      $enrollment = $course->getEnrollment ($this->getUser ());

      if ($course->checkWriteGradesAbility ($this, $this->getUser (), $enrollment)) {
         $column = GradeColumn::byColumnID ($columnID);

         if (empty ($column)) {
            throw new CinciException ("Delete Column Error",
               "The specified grade column does not exist.");
         }
         
         $column->delete ();
      
      } else {
         throw new CinciException ("Delete Column Error",
            "You do not have permission to make changes to the grade record of this course.");
      }
      
      header ('Refresh: 1; url=' . sprintf ("?action=gradeCourse&courseCode=%s", 
         $course->courseCode));

      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf ("Column Deleted: %s", $column->name));
      new Para ($div, "The grade column has been deleted successfully.");
      
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Please wait...");
      $p = new XMLEntity ($div, 'p');
      $p->setAttribute ('style', 'text-align: center;');
      new Image ($p, 'images/redirect.gif', 'redirecting...');
   }

   /*
    * Create a new grade column.
    */
   protected function actionNewColumn ($contentDiv)
   {
      if (empty ($_GET ['courseID'])) {
         throw new CinciException ("New Grade Column Error", 
            "No course was given in which to insert a new grade column.");
      }

      $courseID = $_GET ['courseID'];

      $course = Course::byCourseID ($courseID);

      if (empty ($course)) {
         throw new CinciException ("New Grade Column Error", 
            "The specified course does not exist.");
      }

      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf ("New Grade Column: %s",
         $course->courseName));
      new GradeColumnForm ($div, '?action=submitNewColumn', $course);
   }

   /*
    * Submit a grade column.
    */
   protected function submitNewColumn ($contentDiv)
   {
      if (empty ($_POST ['courseID'])) {
         throw new CinciException ("New Grade Column Error",
            "No course was given in which to insert a new grade column.");
      }

      $courseID = $_POST ['courseID'];

      $course = Course::byCourseID ($courseID);

      if (empty ($course)) {
         throw new CinciException ("New Grade Column Error",
            "The specified course does not exist.");
      }

      $column = new GradeColumn ();

      $column->courseID = $course->courseID;
      $column->name = $_POST ['columnName'];
      $column->pointsPossible = $_POST ['pointsPossible'];

      $enrollment = $course->getEnrollment ($this->getUser ());

      if ($course->checkWriteGradesAbility ($this, $this->getUser (), $enrollment)) {
         
         $column->insert ();
      
      } else {
         throw new CinciException ("New Grade Column Error",
            "You do not have permission to make changes to the grade record of this course.");
      }

      header ('Refresh: 1; url=' . sprintf ("?action=gradeCourse&courseCode=%s", 
         $course->courseCode));

      $div = new Div ($contentDiv, 'prompt');
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, sprintf ("New Grade Column: %s", $column->name));
      new Para ($div, "The grade column has been created successfully.");
      
      $p = new XMLEntity ($div, 'p');
      new TextEntity ($p, "Please wait...");
      $p = new XMLEntity ($div, 'p');
      $p->setAttribute ('style', 'text-align: center;');
      new Image ($p, 'images/redirect.gif', 'redirecting...');
   }

   /*
    * Save the given grade to the database via an AJAX request.
    */
   protected function AJAX_saveGrade ($ajaxReply) 
   {
      global $SiteLog;

      $SiteLog->logDebug ("AJAX_saveGrade method called.");

      if (empty ($_GET ['cellIdentity']) or empty ($_GET ['grade'])) {
         throw new CinciException ("Grade Submit Error",
            "Invalid grade submission request.");
      }

      // Retrieve the cell's identity, a tuple of courseID, columnID, and userID
      // for the grade cell.
      $cellIdentity = $_GET ['cellIdentity'];
      $grade = $_GET ['grade'];

      list ($courseID, $columnID, $userID) = explode (':', $cellIdentity);

      // Retrieve the course and determine if we have rights to edit grades.
      $course = Course::byCourseID ($courseID);

      if (empty ($course)) {
         throw new CinciException ("Grade Submit Error",
            "The specified course does not exist.");
      }

      // Get the user's enrollment in the course.  It's okay if this is NULL,
      // as this represents that the user is not enrolled in the course.
      // Admins are not enrolled in courses typically, but still have permission
      // to write grades granted by the '_adminWriteGradesAbility' action.
      $enrollment = $course->getEnrollment ($this->getUser ());

      // Determine if we can write the grade record in the course.
      if ($course->checkWriteGradesAbility ($this, $this->getUser (), $enrollment)) {
         // Get the user for which the grade will be set.
         $SiteLog->logDebug (sprintf ("Grade write ability granted."));
         
         $column = GradeColumn::byColumnID ($columnID);
         
         // Get the user for which the grade will be set.
         $SiteLog->logDebug (sprintf ("Saving user grade."));
         
         if (empty ($column)) {
            throw new CinciException ("Grade Submit Error",
               "The specified grade column does not exist.");
         }

         $user = User::byUserID ($userID);

         if (empty ($user)) {
            throw new CinciException ("Grade Submit Error",
               "The specified user does not exist.");
         }

         // Confirm that the user is enrolled in the course.
         $enrollment = $course->getEnrollment ($user);
         if (empty ($enrollment)) {
            throw new CinciException ("Grade Submit Error",
               "The specified user is not enrolled in the course.");
         }

         if (! isValidGrade ($grade)) {
            throw new CinciException ("Grade Submit Error",
               "Invalid grade expression.  A grade should be a positive or negative number with an optional 2-digit decimal component.");
         }
         
         // Sets the user's grade for the column.
         // If no grade exists, the grade is created.
         $column->setUserGrade ($user, $grade);

      } else {
         throw new CinciException ("Grade Submit Error",
            "You do not have permission to write to the grade record of this course.");

      }

      // The user's grade was saved successfully.
      // Construct a successful AJAXReply.
      new AJAXStatus ($ajaxReply, 'success');
      new AJAXHeader ($ajaxReply, 'Success');
      new AJAXMessage ($ajaxReply, 'Grade submitted successfully!');
      $gradeXML = new XMLEntity ($ajaxReply, 'grade');
      new TextEntity ($gradeXML, sprintf ("%.2f", $grade));
   }

   protected function AJAX_submitContentSortOrder ($ajaxReply) {
      global $SiteLog;
      
      if (empty ($_POST ['sort']) or empty ($_GET ['path'])) {
         throw new CinciException ("Content Sort Error",
            "No sort order info was provided.");
      }

      $sortOrder = $_POST ['sort'];

      $SiteLog->logInfo (sprintf ("LRS-DEBUG: %s", var_export ($sortOrder, true)));

      // Convert the path to an array, and remove any empty path
      // names therein.  This allows leniency in leading and trailing
      // slashes, e.g. "/course/folder/" is the same as "course/folder".
      $pathArray = explode ('/', $_GET ['path']);
      $pathArray = array_diff ($pathArray, array (''));

      if (count ($pathArray) < 1) {
         throw new CinciException ("Content Sort Error", "No path specified.");
      }

      $user = User::byUserID ($_SESSION ['userid']);

      list ($content, $course, $enrollment) = Course::getPath ($this, $user, $pathArray);
     
      if (! $content->checkWriteAccess ($this, $user, $course, $enrollment)) {
         throw new CinciException ("Content Sort Error",
            "You do not have permission to write to items in this path.");
      }

      if ($content->typeID != CONTENT_TYPE_FOLDER) {
         throw new CinciException ("Content Sort Error",
            "The given path is not a folder.");
      }

      $contentIDMap = array ();
      $folderContents = $content->getFolderContents ();

      foreach ($folderContents as $content) {
         $contentIDMap [$content->contentID] = $content;
      }

      for ($x = 0; $x < sizeof ($sortOrder); $x++) {
         if (array_key_exists ($sortOrder [$x], $contentIDMap)) {
            $content = $contentIDMap [$sortOrder [$x]];
            $content->sortOrder = $x;

         } else {
            throw new CinciException ("Content Sort Error",
               "A given content ID does not exist in this folder path.");
         } 
      }

      foreach ($folderContents as $content) {
         $content->save (false);
      }

      // Construct a successful AJAXReply.
      new AJAXStatus ($ajaxReply, 'success');
      new AJAXHeader ($ajaxReply, 'Success');
      new AJAXMessage ($ajaxReply, 'Sort order submitted successfully.');
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
               new HyperlinkAction (sprintf ("?action=view&path=%s",
               htmlentities ($course->courseCode)))
            );
         }
      }

      return $coursesMenu;
   }
}

?>
