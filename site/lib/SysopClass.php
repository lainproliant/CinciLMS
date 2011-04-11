<?php

/*
 * SysopClass: Defines the actions and properties of a sysop in the
 *             Cincinnatus Learning Management System.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "util/XMLEntity.php";
include_once "UserClass.php";
include_once "Exceptions.php";
include_once "CourseForm.php";
include_once "Course.php";
include_once "Content.php";

class SysopClass extends UserClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
         '_sysopReadWrite'       => NULL,
         '_sysopEnrollAbility'   => NULL,
         'newCourse'             => 'actionNewCourse',
         'editCourse'            => 'actionEditCourse',
         'searchUsers'           => 'actionSearchUsers',
         'submitNewCourse'       => 'submitNewCourse',
         'submitCourseEdit'      => 'submitCourseEdit',
         'submitUserSearch'      => 'submitUserSearch'));
      
      $createMenu = $this->getMenu ()->getItem ('Create');
      $searchMenu = $this->getMenu ()->getItem ('Search');
      
      if (empty ($createMenu)) {
         $createMenu = new ActionMenu ();
         $this->getMenu ()->addItem ('Create', $createMenu);
      }

      if (empty ($searchMenu)) {
         $searchMenu = new ActionMenu ();
         $this->getMenu ()->addItem ('Search', $searchMenu);
      }

      $createMenu->addItem (
         "New Course", 'newCourse');

      $searchMenu->addItem (
         "Users", 'searchUsers');
   }

   protected function actionNewCourse ($contentDiv)
   {
      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Create a New Course");
      new Para ($div, "Edit the course properties below, then click Submit.");
      new CourseForm ($div, '?action=submitNewCourse', $this, $this->getUser ());
   }

   protected function actionSearchUsers ($contentDiv)
   {
      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Search Users in System");
      new Para ($div, "Enter the search criterion below, then click Search.");
      new UserSearchForm ($div, '?action=submitUserSearch');
   }

   protected function submitNewCourse ($contentDiv) 
   {   
      // Get a user instance to confirm that the user actually
      // exists when we create the course in their name.
      $owner = User::byUsername ($_POST ['courseOwner']);
      $accessFlags = implode (',', $_POST ['accessFlags']);

      $courseName = $_POST ['courseName'];
      $courseCode = $_POST ['courseCode'];

      if (empty ($courseCode)) {
         $courseCode = anumfilter ($courseName);
      }
      
      try {
         $course = Course::createNewCourse (
            $courseName,
            $courseCode,
            $accessFlags,
            $owner);

         // Enroll the user as an instructor in the course.
         $course->enrollUser ($owner, COURSE_ROLE_INSTRUCTOR);

      } catch (DAOException $e) {
         throw new CinciDatabaseException ("Course Creation Error", 
            "There was an error creating the new course.",
            $e->error);
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Success!");
      new Para ($div, sprintf (
         "The course \"%s\" was created successfully.", htmlentities ($course->courseName)));
      $p = new XMLEntity ($div, 'p');
      new TextLink ($p, 'index.php', 'Return Home');
   }

   protected function submitUserSearch ($contentDiv)
   {
      $criterion = $_POST ['criterion'];
      $search = $_POST ['search'];

      $users = NULL;
      
      switch ($criterion) {
      case 0:
         $users = User::searchByUsername ($search);
         break;

      case 1:
         $users = User::searchByLastname ($search);
         break;

      case 2:
         $users = User::searchByFullName ($search);
         break;

      default:
         throw new CinciException ("User Search Error",
            "Unknown search criterion.");
      }

      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "User Search Results");
      new UserSearchResults ($div, $this, $users); 
   }
}

