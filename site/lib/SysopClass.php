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

class SysopClass extends UserClass {
   function __construct ()
   {
      parent::__construct ();

      $this->addActions (array (
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
      $course = new Course ();

      # TODO: Create the course entry point here.
      $course->courseName = $_POST ['courseName'];
      $course->accessFlags = implode (',', $_POST ['accessFlags']);

      
      try {
         $course->insert ();

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

