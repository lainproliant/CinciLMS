<?php

/*
 * CoursesJoinDAO: Left-joining methods against the Courses table.
 */
class CourseJoinDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }

   /*
    * Joins the given user's course enrollments with the matching
    * courses.  Returns an array of two result arrays, used
    * by CourseJoinVO to create an array of FactCourseEnrollmentVO
    * and an array of Course objects, respectively.
    */
   public function joinUserID_CourseEnrollment ($userID)
   {
      $resultArrays = array ();
      $resultArrays [0] = array ();
      $resultArrays [1] = array ();

      $enrollmentResults = array ();
      $courseResults = array ();

      $query = 
         <<<QRY
         select
            FactCourseEnrollment.UserID,
            FactCourseEnrollment.CourseID,
            FactCourseEnrollment.RoleID,
            FactCourseEnrollment.AccessFlags,
            Courses.CourseID,
            Courses.CourseCode,
            Courses.CourseName,
            Courses.EntryPointID,
            Courses.AccessFlags

         from FactCourseEnrollment, Courses
         
         where
            FactCourseEnrollment.CourseID = Courses.CourseID
         and
            FactCourseEnrollment.UserID = ?;
QRY;

      $stmt = $this->db->prepare ($query);
      $stmt->bind_param ("i", $userID);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result (
         $enrollmentResults ["UserID"],
         $enrollmentResults ["CourseID"],
         $enrollmentResults ["RoleID"], 
         $enrollmentResults ["AccessFlags"],
         $courseResults ["CourseID"],
         $courseResults ["CourseCode"],
         $courseResults ["CourseName"],
         $courseResults ["EntryPointID"],
         $courseResults ["AccessFlags"]);

      while ($stmt->fetch ()) {
         $resultArrays [0][] = array_map ($lambda, $enrollmentResults);
         $resultArrays [1][] = array_map ($lambda, $courseResults);
      }

      $stmt->close ();
      return $resultArrays;
   }
}

?>
