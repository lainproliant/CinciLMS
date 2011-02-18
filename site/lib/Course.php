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

function enumerateCoursePermissions ()
{
   return array (
      'UR'    => 'Owner Read',
      'UW'    => 'Owner Write',
      'MR'    => 'Member Read',
      'MW'    => 'Member Write',
      'OR'    => 'Others Read',
      'GR'    => 'Guests Read');
}

class Course extends CoursesVO {
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
}

?>

