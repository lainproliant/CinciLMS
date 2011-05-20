<?php

/*
 * Course: A class representation of a course in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "VO/CoursesVO.php";
include_once "Content.php";

define ("COURSE_ROLE_STUDENT", 1);
define ("COURSE_ROLE_INSTRUCTOR", 2);
define ("COURSE_ROLE_BUILDER", 3);
define ("COURSE_ROLE_ASSISTANT", 4);
define ("COURSE_ROLE_GUEST", 5);

define ("COURSE_DEFAULT_PERMISSIONS", "UR,UW,MR");

/*
 * Enumerate names of course roles.
 */
function enumerateCourseRoles ()
{
   return array (
      COURSE_ROLE_STUDENT     => 'Student',
      COURSE_ROLE_INSTRUCTOR  => 'Instructor',
      COURSE_ROLE_BUILDER     => 'Course Builder',
      COURSE_ROLE_ASSISTANT   => 'Teaching Assistant');
}

/*
 * Enumerate permissions used for courses.
 */
function enumerateCoursePermissions ()
{
   return array (
      'UR'    => 'Builder Read',
      'UW'    => 'Builder Write',
      'MR'    => 'Student Read',
      'MW'    => 'Student Write',
      'OR'    => 'Others Read',
      'GR'    => 'Guests Read');
}

/*
 * Enumerate permissions used for course content.
 */
function enumerateCourseContentPermissions ()
{
   return array (
      'UR'    => 'Owner Read',
      'UW'    => 'Owner Write',
      'MR'    => 'Member Read',
      'MW'    => 'Member Write',
      'OR'    => 'Others Read',
      'GR'    => 'Guests Read');
}

/*
 * Enumerate per-user permissions for course enrollments.
 */
function enumerateUserPermissions ()
{
   return array (
      'CR'     => 'Course Read',
      'CW'     => 'Course Write',
      'GrR'    => 'Grades Read',
      'GrW'    => 'Grades Write',
      'EnR'    => 'Enrollment Read',
      'EnW'    => 'Enrollment Write');
}

class Course extends CoursesVO {
   /*
    * Acts as a live constructor for the course.  Creates a new course,
    * inserts it into the database.
    *
    * courseName:       The name of the course.
    * courseCode:       The course code.  Must be unique among courses.
    * accessFlags:      The course access flags.
    * creator:          The User VO for the creator of the course.
    *
    * Returns a VO for the new course.  Throws DAOException if the course
    * could not be inserted.
    */
   public static function createNewCourse ($courseName, $courseCode, 
      $accessFlags, $creator)
   {
      $course = new static ();

      $course->courseName = $courseName;
      $course->courseCode = $courseCode;
      $course->accessFlags = $accessFlags; 

      // Create an entry point as the root folder of the course's content.
      $entryPoint = new ContentFolder ();
      $entryPoint->name = "course-root";
      $entryPoint->ownerID = $creator->userID;
      $entryPoint->insert ();

      $course->entryPointID = $entryPoint->contentID;
      
      try {
         $course->insert ();

      } catch (DAOException $e) {
         // Delete the entry point we created for the course, then
         // re-throw the exception.
         $entryPoint->delete ();

         throw $e;
      }

      return $course;
   }

   /*
    * Enrolls the given user in the course with the given role.
    *
    * user:       The User to enroll in the course.
    * roleID:     The ID of the role to assign the user.
    *
    * Returns a new FactCourseEnrollmentVO object on success,
    * throws a DAOException upon failure.
    *
    * NOTE that this method does not check for an existing
    * enrollment, as the composite key of UserID/CourseID
    * in FactCourseEnrollment is forced to be unique.  If
    * such an enrollment exists, a DAOException will be thrown.
    */
   public function enrollUser ($user, $roleID)
   {
      $enrollment = new FactCourseEnrollmentVO ();
      $enrollment->userID = $user->userID;
      $enrollment->courseID = $this->courseID;
      $enrollment->roleID = $roleID;

      $roleInfo = CourseRolesVO::byRoleID ($roleID);
      $enrollment->accessFlags = $roleInfo->defaultAccess;

      $enrollment->insert ();

      return $enrollment;
   }

   /*
    * Unenrolls the given user from the course.
    *
    * user:       The user to unenroll.
    *
    * Throws DAOException upon failure.
    */
   public function unenrollUser ($user)
   {
      $enrollment = FactCourseEnrollmentVO::byUserID_CourseID (
         $user->userID, $this->courseID);

      $enrollment->delete ();
   }

   /*
    * Lists all of the Users enrolled in the course.
    *
    * Returns a list of FactCourseEnrollmentVO objects, 
    * throws DAOException upon failure.
    */
   public function getUserEnrollments ()
   {
      return FactCourseEnrollmentVO::listByCourseID ($this->courseID);
   }

   /*
    * Checks to see if the given user is enrolled in the course.
    *
    * Returns a FactCourseEnrollmentVO instance for the user's enrollment
    * if they are enrolled, or NULL if they are not enrolled.
    *
    * Throws DAOException if there is an error querying the database.
    */
   public function getEnrollment ($user)
   {
      $enrollment = FactCourseEnrollmentVO::byUserID_CourseID (
         $user->userID, $this->courseID);
      
      // If the query failed, a primary key will be NULL.
      if (empty ($enrollment->userID)) {
         // The user is not enrolled in this course.  Return NULL.
         return NULL;
      } else {
         return $enrollment;
      }
   }
   
   /*
    * Displays the contents of the course and its course-root folder in 
    * the given div.
    *
    * contentDiv:       The div in which to display.
    * authority:        The user's AuthorityClass instance.
    * user:             The user for which permissions and behavior
    *                   will be defined.
    * pathArray:        The path to be displayed relative to the course root.
    *                   If pathArray is not specified, the course root is displayed.
    */
   public function display ($contentDiv, $authority, $user, $pathArray)
   {
      $contentInfo = Course::getPath ($authority, $user, $pathArray);
      $content = $contentInfo [0];
      $course = $contentInfo [1];
      $enrollment = $contentInfo [2];

      // Construct an absolute path to pass to the item.
      $absolutePath = implode ('/', $pathArray);

      // Add the absolute path as a known pathName to the content item.
      $content->pathName = $absolutePath;
      
      // Ask the content item to add appropriate actions to the menu bar.
      $content->addContext ($authority, $user, $this, $enrollment);

      // Ask this course to add appropriate actions to the menu bar.
      $this->addContext ($authority, $user, $enrollment);

      // Ask the path to print itself.
      $content->display ($contentDiv, $absolutePath, $authority, $user, $this, $enrollment);
   }
   
   /*
    * Get the content item, course, and enrollment associated with the given user role
    * and path.
    *
    * authority:        The AuthorityClass instance for the user accessing the path.
    * user:             The user accessing the path.
    * patnArray:        The path to be accessed.
    *
    * Returns a tuple of content, course, and enrollment, in that order.
    * Throws CinciAccessException if the content is not accessible by this user.
    */
   public static function getPath ($authority, $user, $pathArray)
   {
      $courseCode = array_shift ($pathArray);

      $course = Course::byCourseCode ($courseCode);
      
      if (empty ($course->courseID)) {
         throw new CinciAccessException ("The specified course does not exist.");
      }
      
      $enrollment = $course->getEnrollment ($user);
      
      if (empty ($enrollment) and ! $authority->authorizeCheck ('_sysopReadWrite')) {
         throw new CinciAccessException (
            "You are not authorized to access this course.");
      }

      // If the entry point doesn't exist, a CinciAccessException will be thrown.
      $entryPoint = CourseContent::byContentID ($course->entryPointID)->resolve ();

      if (empty ($entryPoint->contentID)) {
         throw new CinciAccessException (
            "The course's course-root is missing.  Please contact a system administrator.");
      }

      $content = $entryPoint;
      
      if (count ($pathArray) > 0) {
         $content = $entryPoint->resolvePath ($pathArray,
            $authority, $user, $course, $enrollment);
      }

      return array ($content, $course, $enrollment);
   }

   /*
    * Checks whether the specified user has the rights to enroll users
    * in the specified course, based on the given course enrollment.
    *
    * authority:     The user's current AuthorityClass instance.  Can be NULL,
    *                in which case special AuthorityClass permissions are not
    *                determined.
    * user:          The User instance.
    * enrollment:    The user's enrollment in the course.  May be NULL, in which
    *                case the user is treated as a guest.
    *
    * Returns True if the user has the right to enroll users, False otherwise.
    */
   public function checkEnrollAbility ($authority, $user, $enrollment)
   {
      // If the authority contains '_sysopEnrollAbility' permissions, grant ability.
      if (! empty ($authority) and $authority->authorizeCheck ('_sysopEnrollAbility')) {
         return TRUE;
      }

      // If the user is not enrolled in the course, do not allow them to enroll anyone.
      // Only allow them to enroll if they have EnW rights.

      $userPermissions = empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);

      if (in_array ('EnW', $userPermissions)) {
         // The user is enrolled and has EnW permissions, grant ability.
         return TRUE;
      } else {
         // The user is not enrolled or does not have EnW permissions.  Deny ability.
         return FALSE;
      }
   }

   /*
    * Checks whether the specified user has the rights to unenroll users
    * from the specified course, based on the given course enrollment.
    *
    * authority:     The user's current AuthorityClass instance.  Can be NULL,
    *                in which case special AuthorityClass permissions are not
    *                determined.
    * user:          The User instance.  Can be NULL, in which case the user is
    *                treated as a guest (and definitely won't have unenroll ability).
    * enrollment:    The user's enrollment in the course.  May be NULL, in which
    *                case the user is treated as a guest.
    *
    * Returns True if the user has the right to unenroll users, False otherwise.
    */
   public function checkUnenrollAbility ($authority, $user, $enrollment)
   {
      // If the authority contains '_adminUnenrollAbility' permissions, grant ability.
      if (! empty ($authority) and $authority->authorizeCheck ('_adminUnenrollAbility')) {
         return TRUE;
      }

      // If the user is not enrolled in the course, do not allow them to unenroll anyone.
      // Only allow them to unenroll if they have EnW rights.

      $userPermissions = empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);

      if (in_array ('EnW', $userPermissions)) {
         // The user is enrolled and has EnW permissions, grant ability.
         return TRUE;
      } else {
         // The user is not enrolled or does not have EnW permissions.  Deny ability.
         return FALSE;
      }
   }

   /*
    * Checks whether the specified user has rights to read the grade record
    * for the specified course, based on the given course enrollment.
    *
    * authority:     The user's current AuthorityClass instance.  Can be NULL,
    *                in which case special AuthorityClass permissions are not
    *                determined.
    * user:          The User instance.  Can be NULL, in which case the user is
    *                treated as a guest (and definitely won't have unenroll ability).
    * enrollment:    The user's enrollment in the course.  May be NULL, in which
    *                case the user is treated as a guest.
    */
   public function checkReadGradesAbility ($authority, $user, $enrollment)
   {
      // If the authority contains '_sysopReadGradesAbility', grant ability.
      if (! empty ($authority) and $authority->authorizeCheck ('_sysopReadGradesAbility')) {
         return TRUE;
      }

      $userPermissions = empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);

      if (in_array ('GrR', $userPermissions)) {
         // The user is enrolled and has GrR permissions, grant ability.
         return TRUE;
      } else {
         // The user is not enrolled or does not have GrR permissions.  Deny ability.
         return FALSE;
      }
   }
   
   /*
    * Checks whether the specified user has rights to edit the grade record
    * for the specified course, based on the given course enrollment.
    *
    * authority:     The user's current AuthorityClass instance.  Can be NULL,
    *                in which case special AuthorityClass permissions are not
    *                determined.
    * user:          The User instance.  Can be NULL, in which case the user is
    *                treated as a guest (and definitely won't have unenroll ability).
    * enrollment:    The user's enrollment in the course.  May be NULL, in which
    *                case the user is treated as a guest.
    */
   public function checkWriteGradesAbility ($authority, $user, $enrollment)
   {
      // If the authority contains '_sysopReadGradesAbility', grant ability.
      if (! empty ($authority) and $authority->authorizeCheck ('_adminWriteGradesAbility')) {
         return TRUE;
      }

      $userPermissions = empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);

      if (in_array ('GrW', $userPermissions)) {
         // The user is enrolled and has GrW permissions, grant ability.
         return TRUE;
      } else {
         // The user is not enrolled or does not have GrW permissions.  Deny ability.
         return FALSE;
      }
   }

   /*
    * Adds course-specific context items to the action menu.
    */
   public function addContext ($authority, $user, $enrollment)
   {
      if ($this->checkEnrollAbility ($authority, $user, $enrollment)) {
         $modifyMenu = $authority->getMenu ()->appendSubmenu ('Modify');
         
         $enrollUserAction = sprintf ('?action=enrollUser&courseCode=%s', 
            htmlentities ($this->courseCode));

         // Add course enrollment item.
         $modifyMenu->addItem ('Enroll User', new HyperlinkAction ($enrollUserAction));
      }

      if ($this->checkReadGradesAbility ($authority, $user, $enrollment)) {
         $gradeCourseAction = sprintf ("?action=gradeCourse&courseCode=%s",
            htmlentities ($this->courseCode));

         $authority->getMenu ()->addItem ("Grade", new HyperlinkAction ($gradeCourseAction));
      }
   }
}

?>
