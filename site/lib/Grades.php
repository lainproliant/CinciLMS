<?php

/*
 * Grades: Classes representing grades and grade columns.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "VO/GradesVO.php";

class Grade extends GradesVO {

}

class GradeColumn extends GradeColumnsVO {
   /*
    * Fetches the grade in a column for a particular user.
    */
   public function fetchUserGrade ($user) {
      return Grade::byColumnID_StudentID ($this->columnID, $user->userID);
   }

   /*
    * Sets the grade for a user in this column.  If no grade exists,
    * a grade is created and given the specified value.
    */
   public function setUserGrade ($user, $grade)
   {
      global $SiteLog;

      $gradeCell = $this->fetchUserGrade ($user);

      $SiteLog->logDebug (sprintf ("setUserGrade: grade = %s",
         var_export ($gradeCell, true)));
      
      try {
         if (empty ($gradeCell)) {
            $gradeCell = new Grade ();

            $gradeCell->columnID = $this->columnID;
            $gradeCell->studentID = $user->userID;
            $gradeCell->grade = $grade;

            $gradeCell->insert ();
         } else {

            $gradeCell->grade = $grade;

            $gradeCell->save ();
         }
      } catch (Exception $e) {
         $SiteLog->logError (sprintf ("ERROR: %s",
            var_export ($e, true)));
      }
   }
}

/*
 * A class representing a grade record for a course.
 */
class GradeRecord {
   private $gradeColumns;
   private $course;

   /*
    * Constructor.  Gathers a list of all of the grade columns
    *    for the specified course.
    *
    * course:  The course from which grade columns will be sampled.1
    */
   function __construct ($course) {
      $this->course = $course;
      $this->gradeColumns = GradeColumn::listByCourseID ($course->courseID);
   }

   /*
    * Fetches a list of all of the grade columns.
    */
   public function getColumns ()
   {
      return $this->gradeColumns;
   }


   /*
    * Fetches a grade row for the specified user.
    *
    * Returns an array of 2-tuples, containing a references to each grade
    * row and grade value for a particular user.
    *
    * NOTE: This method disregards the user's enrollment in the course.
    *    It will return an "empty" grade row for a user if the user
    *    has no grades in the course, whether or not they are enrolled.
    *    It will also return grade columns for a user who is no longer
    *    enrolled in the course so long as their grade records remain.
    */
   public function fetchUserGradeRow ($user) {
      $gradeRow = array ();

      foreach ($this->gradeColumns as $gradeColumn) {
         $gradeRow [] = array ($gradeColumn, $gradeColumn->fetchUserGrade ($user));   
      }

      return $gradeRow;
   }

   /*
    * Adds grade-specific context items to the action menu.
    */
   public function addContext ($authority) {
      $createMenu = $authority->getMenu ()->appendSubmenu ('Create');

      $newColumnAction = sprintf ("?action=newColumn&courseID=%s",
         htmlentities ($this->course->courseID));

      $createMenu->addItem ('New Grade Column', 
         new JavascriptAction (sprintf ("javascript:showNewColumn(%d);",
            $this->course->courseID)));
   }
}

?>
