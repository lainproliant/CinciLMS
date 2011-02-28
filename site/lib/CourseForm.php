<?php

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";
include_once "Course.php";

/*
 * CourseForm: Presents a form with which to create courses
 *             and edit course properties.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

class CourseForm extends Form {
   function __construct ($parent, $action, $userClass, $user, $course = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'user_form');

      # Include the user validation script.
      new Script ($this, 'lib/course.js');

      $this->setAttribute ('onSubmit', 'return courseFormValidate (this);');
      
      $courseName = NULL; 
      $courseCode = NULL;
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;

      if (! empty ($course)) {
         $courseName = $course->courseName;
         $courseCode = $course->courseCode;
         $accessFlags = $course->accessFlags;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Name:', 'courseName', 'first');
      new TextInput ($div, 'courseName', 'courseName', $courseName);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Code:', 'courseCode');
      new TextInput ($div, 'courseCode', 'courseCode', $courseCode);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Owner:', 'courseOwner');
      new TextInput ($div, 'courseOwner', 'courseOwner', $user->username);

      $div = new Div ($listDiv, 'row');
      $coursePermissions = enumerateCoursePermissions ();
      new Label ($div, 'Course Permissions:');
      $stack = new Div ($div, 'stack');
      new Hr ($stack);
      foreach ($coursePermissions as $flag => $desc) {
         $check = new Checkbox ($stack, 'accessFlags[]', NULL, $flag);
         if (strpos ($accessFlags, $flag) !== false) {
            $check->setAttribute ('checked', '1');
         }

         new TextEntity ($stack, $desc);
         new Br ($stack);
      }
      new Hr ($stack);

      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      if (! empty ($course)) {
         new HiddenField ($this, "courseID", NULL, $course->courseID);
      }
   }
}

class EnrollmentForm extends Form {
   function __construct ($parent, $action, $userClass, $user, $course = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'user_form');

      # Include the user validation script.
      new Script ($this, 'lib/course.js');

      $this->setAttribute ('onSubmit', 'return courseFormValidate (this);');
      
      $courseName = NULL; 
      $courseCode = NULL;
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;

      if (! empty ($course)) {
         $courseName = $course->courseName;
         $courseCode = $course->courseCode;
         $accessFlags = $course->accessFlags;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Name:', 'courseName', 'first');
      new TextInput ($div, 'courseName', 'courseName', $courseName);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Code:', 'courseCode');
      new TextInput ($div, 'courseCode', 'courseCode', $courseCode);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Owner:', 'courseOwner');
      new TextInput ($div, 'courseOwner', 'courseOwner', $user->username);

      $div = new Div ($listDiv, 'row');
      $coursePermissions = enumerateCoursePermissions ();
      new Label ($div, 'Course Permissions:');
      $stack = new Div ($div, 'stack');
      new Hr ($stack);
      foreach ($coursePermissions as $flag => $desc) {
         $check = new Checkbox ($stack, 'accessFlags[]', NULL, $flag);
         if (strpos ($accessFlags, $flag) !== false) {
            $check->setAttribute ('checked', '1');
         }

         new TextEntity ($stack, $desc);
         new Br ($stack);
      }
      new Hr ($stack);

      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      if (! empty ($course)) {
         new HiddenField ($this, "courseID", NULL, $course->courseID);
      }
   }
}

?>
