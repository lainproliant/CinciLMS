<?php

/*
 * Course: A class representation of a course in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "VO/CoursesVO.php";

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
}

?>

