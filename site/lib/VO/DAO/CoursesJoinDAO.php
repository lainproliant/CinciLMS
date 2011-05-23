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
   
   public function searchByUsername ($courseID, $search)
   {
      $resultArrays = array ();
      $resultArrays [0] = array ();
      $resultArrays [1] = array ();

      $enrollmentResults = array ();
      $userResults = array ();

      $query = 
         <<<QRY
         select
            FactCourseEnrollment.UserID,
            FactCourseEnrollment.CourseID,
            FactCourseEnrollment.RoleID,
            FactCourseEnrollment.AccessFlags,
            Users.UserID,
            Users.ExternalID,
            Users.Username,
            Users.FirstName,
            Users.MiddleInitial,
            Users.LastName,
            Users.EmailAddress,
            Users.PasswordSalt,
            Users.PasswordHash,
            Users.Notes,
            Users.LastLogin,
            Users.IsActive,
            Users.SystemRole

         from FactCourseEnrollment, Users
          
         where
            FactCourseEnrollment.CourseID = ?
         and
            FactCourseEnrollment.UserID = Users.UserID
         and
            Users.Username like ?
QRY;

      $stmt = $this->db->prepare ($query);
      $match = sprintf ("%s%%", $search); 
      $stmt->bind_param ("is", $courseID, $match);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result (
         $enrollmentResults ["UserID"],
         $enrollmentResults ["CourseID"],
         $enrollmentResults ["RoleID"], 
         $enrollmentResults ["AccessFlags"],
         $userResults ["UserID"],
         $userResults ["ExternalID"],
         $userResults ["Username"],
         $userResults ["FirstName"],
         $userResults ["MiddleInitial"],
         $userResults ["LastName"],
         $userResults ["EmailAddress"],
         $userResults ["PasswordSalt"],
         $userResults ["PasswordHash"],
         $userResults ["Notes"],
         $userResults ["LastLogin"],
         $userResults ["IsActive"],
         $userResults ["SystemRole"]);

      while ($stmt->fetch ()) {
         $resultArrays [0][] = array_map ($lambda, $enrollmentResults);
         $resultArrays [1][] = array_map ($lambda, $userResults);
      }

      $stmt->close ();
      return $resultArrays;
   }

   public function searchByLastName ($courseID, $search)
   {
      $resultArrays = array ();
      $resultArrays [0] = array ();
      $resultArrays [1] = array ();

      $enrollmentResults = array ();
      $userResults = array ();

      $query = 
         <<<QRY
         select
            FactCourseEnrollment.UserID,
            FactCourseEnrollment.CourseID,
            FactCourseEnrollment.RoleID,
            FactCourseEnrollment.AccessFlags,
            Users.UserID,
            Users.ExternalID,
            Users.Username,
            Users.FirstName,
            Users.MiddleInitial,
            Users.LastName,
            Users.EmailAddress,
            Users.PasswordSalt,
            Users.PasswordHash,
            Users.Notes,
            Users.LastLogin,
            Users.IsActive,
            Users.SystemRole

         from FactCourseEnrollment, Users
         
         where
            FactCourseEnrollment.CourseID = ?
         and
            FactCourseEnrollment.UserID = Users.UserID
         and
            Users.LastName like ?
QRY;

      $stmt = $this->db->prepare ($query);
      $match = sprintf ("%s%%", $search); 
      $stmt->bind_param ("is", $courseID, $match);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result (
         $enrollmentResults ["UserID"],
         $enrollmentResults ["CourseID"],
         $enrollmentResults ["RoleID"], 
         $enrollmentResults ["AccessFlags"],
         $userResults ["UserID"],
         $userResults ["ExternalID"],
         $userResults ["Username"],
         $userResults ["FirstName"],
         $userResults ["MiddleInitial"],
         $userResults ["LastName"],
         $userResults ["EmailAddress"],
         $userResults ["PasswordSalt"],
         $userResults ["PasswordHash"],
         $userResults ["Notes"],
         $userResults ["LastLogin"],
         $userResults ["IsActive"],
         $userResults ["SystemRole"]);

      while ($stmt->fetch ()) {
         $resultArrays [0][] = array_map ($lambda, $enrollmentResults);
         $resultArrays [1][] = array_map ($lambda, $userResults);
      }

      $stmt->close ();
      return $resultArrays;
   }
   
   public function searchByFullName ($courseID, $firstSearch, $lastSearch)
   {
      $resultArrays = array ();
      $resultArrays [0] = array ();
      $resultArrays [1] = array ();

      $enrollmentResults = array ();
      $userResults = array ();

      $query = 
         <<<QRY
         select
            FactCourseEnrollment.UserID,
            FactCourseEnrollment.CourseID,
            FactCourseEnrollment.RoleID,
            FactCourseEnrollment.AccessFlags,
            Users.UserID,
            Users.ExternalID,
            Users.Username,
            Users.FirstName,
            Users.MiddleInitial,
            Users.LastName,
            Users.EmailAddress,
            Users.PasswordSalt,
            Users.PasswordHash,
            Users.Notes,
            Users.LastLogin,
            Users.IsActive,
            Users.SystemRole

         from FactCourseEnrollment, Users
         
         where
            FactCourseEnrollment.CourseID = ?
         and
            FactCourseEnrollment.UserID = Users.UserID
         and
            Users.FirstName like ?
         and
            Users.LastName like ?
QRY;

      $stmt = $this->db->prepare ($query);
      $matchA = sprintf ("%s%%", $firstSearch);
      $matchB = sprintf ("%s%%", $lastSearch);
      $stmt->bind_param ("iss", $courseID, $matchA, $matchB);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result (
         $enrollmentResults ["UserID"],
         $enrollmentResults ["CourseID"],
         $enrollmentResults ["RoleID"], 
         $enrollmentResults ["AccessFlags"],
         $userResults ["UserID"],
         $userResults ["ExternalID"],
         $userResults ["Username"],
         $userResults ["FirstName"],
         $userResults ["MiddleInitial"],
         $userResults ["LastName"],
         $userResults ["EmailAddress"],
         $userResults ["PasswordSalt"],
         $userResults ["PasswordHash"],
         $userResults ["Notes"],
         $userResults ["LastLogin"],
         $userResults ["IsActive"],
         $userResults ["SystemRole"]);

      while ($stmt->fetch ()) {
         $resultArrays [0][] = array_map ($lambda, $enrollmentResults);
         $resultArrays [1][] = array_map ($lambda, $userResults);
      }

      $stmt->close ();
      return $resultArrays;
   }
}
?>
