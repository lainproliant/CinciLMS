<?php

/*
 * AssignmentExtendedDAO.php: Extended DAO methods for assignments.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3.
 */
include_once "AssignmentDAO.php";

class AssignmentSubmissionsExtendedDAO extends AssignmentFileSubmissionsDAO {

   function __construct () {
      parent::__construct ();
   }

   public function listByCourseID_StudentID_AssignmentID ($courseID, $studentID, $assignmentID)
   {
      return $this->search (array (
         "courseID = ?" => $courseID,
         "studentID = ?" => $studentID,
         "assignmentID = ?" => $assignmentID));
   }

}

?>
