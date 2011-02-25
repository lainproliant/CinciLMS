<?php

include_once "Course.php";
include_once "VO/DAO/CoursesJoinDAO.php";

/*
 * CoursesJoin: The results of left-joining methods 
 *              against the Courses table.
 */
class CourseJoin {
   /*
    * Joins the given user's course enrollments with the matching
    * courses.  Returns an array containing two arrays, of
    * FactCourseEnrollmentVO objects and associated Course objects,
    * respectively.
    */
   public static function joinUserID_CourseEnrollment ($userID)
   {
      $enrollments = array ();
      $courses = array ();
      
      $dao = new CourseJoinDAO ();
      $resultArrays = $dao->joinUserID_CourseEnrollment ($userID);

      foreach ($resultArrays [0] as $enrollmentData) {
         $enrollments [] = FactCourseEnrollmentVO::fromResult (
            $enrollmentData);
      }

      foreach ($resultArrays [1] as $courseData) {
         $courses [] = Course::fromResult ($courseData);
      }

      return array ($enrollments, $courses);
   }
}

?>
