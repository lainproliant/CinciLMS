<?php

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";

/*
 * GradeForm: Presents the grade record and other related forms.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */
class GradeRecordForm extends Div {
   function __construct ($parent, $course) {
      parent::__construct ($parent, "grades");

      $gradeRecord = new GradeRecord ($course);
      $enrollments = $course->getUserEnrollments ();

      $table = new Table ($this, "sortable");

      new TableHeader ($table->getHead (), "First Name");
      new TableHeader ($table->getHead (), "Last Name");
      new TableHeader ($table->getHead (), "Username");

      foreach ($gradeRecord->getColumns () as $column) {
         new TableHeader ($table->getHead (), $column->name);
      }

      /*
       * LRS-TODO: Implement show/hide options for Non-Student
       *    and disabled user enrollments (CR = False).
       */
      foreach ($enrollments as $enrollment) {
         $userPermissions = explode (',', $enrollment->accessFlags);

         if (in_array ('CW', $userPermissions)) {
            $row = new TableRow ($table->getBody ());

            $user = User::byUserID ($enrollment->userID);

            $this->createStaticCell ($row, $user->firstName);
            $this->createStaticCell ($row, $user->lastName);
            $this->createStaticCell ($row, $user->username);
            
            $gradeRow = $gradeRecord->fetchUserGradeRow ($user);
            
            foreach ($gradeRow as $gradeCell) {
               $column = $gradeCell [0];
               $grade = $gradeCell [1];

               $this->createGradeCell ($row, $course, $user, $column, $grade);
            }
         }
      }
   }

   private function createStaticCell ($row, $text)
   {
      $col = new TableColumn ($row);
      $col->setAttribute ('class', 'static');

      new TextEntity ($col, htmlentities ($text));
   }

   private function createGradeCell ($row, $course, $user, $column, $grade)
   {
      $col = new TableColumn ($row);
      
      $col->setAttribute ("class", "editable");
      $col->setAttribute ("metadata", sprintf ("%d:%d:%d",
         $course->courseID,
         $user->userID,
         $column->columnID));

      new TextEntity ($row, htmlentities ($grade->grade));
   }
}


?>
