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

/*
 * Replace all instances of any non-alphanumeric or 
 * underscore character with an underscore.
 * Useful for limiting the characters in course
 * content path names.
 *
 * $string:    The string to filter.
 *
 * Returns a filtered version of the string.
 */
function anumfilter ($string)
{
   return preg_replace ('/[^A-Za-z0-9_-]/', '_', $string);
}

class SysopClass extends UserClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
         '_sysopReadWrite'    => NULL,
         'newCourse'          => 'actionNewCourse',
         'editCourse'         => 'actionEditCourse',
         'submitNewCourse'    => 'submitNewCourse',
         'submitCourseEdit'   => 'submitCourseEdit'));

      $this->getMenu ()->addItem (
         "Courses", new ActionMenu (array (
            "Create a New Course"      => 'newCourse')));
   }

   protected function actionNewCourse ($contentDiv)
   {
      $div = new Div ($contentDiv, "prompt");
      $header = new XMLEntity ($div, 'h3');
      new TextEntity ($header, "Create a New Course");
      new Para ($div, "Edit the course properties below, then click Submit.");
      new CourseForm ($div, '?action=submitNewCourse', $this);
   }

   protected function submitNewCourse ($contentDiv) {
      
      // Get a user instance to confirm that the user actually
      // exists when we create the course in their name.
      $creator = User::byUserID ($_SESSION ['userid']);
      $accessFlags = implode (',', $_POST ['accessFlags']);
      
      try {
         $course = Course::createNewCourse (
            $_POST ['courseName'],
            $_POST ['courseCode'],
            $accessFlags,
            $creator);

         // Enroll the user as an instructor in the course.
         $course->enrollUser ($creator, COURSE_ROLE_INSTRUCTOR);

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
   }
}

