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

   public static function searchByUsername ($courseID, $search)
   {
      $enrollments = array ();
      $users = array ();
      
      $dao = new CourseJoinDAO ();
      $resultArrays = $dao->searchByUsername ($courseID, $search);

      foreach ($resultArrays [0] as $enrollmentData) {
         $enrollments [] = FactCourseEnrollmentVO::fromResult (
            $enrollmentData);
      }

      foreach ($resultArrays [1] as $userData) {
         $users [] = User::fromResult ($userData);
      }

      return array ($enrollments, $users);
   }
   
   public static function searchByLastName ($courseID, $search)
   {
      $enrollments = array ();
      $users = array ();
      
      $dao = new CourseJoinDAO ();
      $resultArrays = $dao->searchByLastName ($courseID, $search);

      foreach ($resultArrays [0] as $enrollmentData) {
         $enrollments [] = FactCourseEnrollmentVO::fromResult (
            $enrollmentData);
      }

      foreach ($resultArrays [1] as $userData) {
         $users [] = User::fromResult ($userData);
      }

      return array ($enrollments, $users);
   }
   
   public static function searchByFullName ($courseID, $search)
   {
      $enrollments = array ();
      $users = array ();
      
      list ($lastSearch, $firstSearch) = explode (",", $search);
      
      $lastSearch = trim ($lastSearch);
      $firstSearch = trim ($firstSearch);

      $dao = new CourseJoinDAO ();
      $resultArrays = $dao->searchByFullName ($courseID, $firstSearch, $lastSearch);

      foreach ($resultArrays [0] as $enrollmentData) {
         $enrollments [] = FactCourseEnrollmentVO::fromResult (
            $enrollmentData);
      }

      foreach ($resultArrays [1] as $userData) {
         $users [] = User::fromResult ($userData);
      }

      return array ($enrollments, $users);
   }
}

?>
