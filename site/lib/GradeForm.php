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
   function __construct ($parent, $course, $authority) {
      parent::__construct ($parent, "grades");

      // Include the required javascript plugins.
      new Script ($this, 'lib/util/js/jquery.tablesorter.js');
      new Script ($this, 'lib/util/js/jquery.timers.js');
      new Script ($this, 'lib/util/js/facebox.js');
      new Script ($this, 'lib/facebox-init.js');
      new Script ($this, 'lib/grade-record.js');
      
      // Create a div for the status message.
      $status = new Div ($this, 'status');
      $status->setAttribute ('id', 'gradeRecordStatus');
      new TextEntity ($status, "Ready.");

      // Create the context menu for columns.
      $this->createColumnContextMenu ();

      // Fetch the grade record and ask it for context menu items.
      $gradeRecord = new GradeRecord ($course);
      $gradeRecord->addContext ($authority);

      $enrollments = $course->getUserEnrollments ();

      $table = new Table ($this, "sortable");

      new TableHeader ($table->getHead (), "First Name");
      new TableHeader ($table->getHead (), "Last Name");
      new TableHeader ($table->getHead (), "Username");

      foreach ($gradeRecord->getColumns () as $column) {
         $header = new TableHeader ($table->getHead ());

         $header->setAttribute ('data-column', sprintf ("%d:%d",
            $course->courseID,
            $column->columnID));

         new Image ($header, 'images/menu-context.png', 'context');
         new Span ($header, $column->name);
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
      $col->setAttribute ("data-cell", sprintf ("%d:%d:%d",
         $course->courseID,
         $column->columnID,
         $user->userID));
      
      if (! empty ($grade)) {
         new TextEntity ($row, htmlentities ($grade->grade));
      }
   }

   private function createColumnContextMenu ()
   {
      $contextMenu = new Div ($this);
      $contextMenu->setAttribute ('id', 'column-context-menu');
      $contextMenu->setAttribute ('class', 'menu');

      $menuList = new UnorderedList ($this, 'L1 context');
   }
}

class GradeColumnForm extends Form {
   function __construct ($parent, $action, $course, $column = NULL)
   {
      parent::__construct ($parent, $action, 'POST', 'grade_column_form');

      new Script ($this, 'lib/grade-column-form.js');

      $columnName = NULL;
      $pointsPossible = 100;

      if (! empty ($column)) {
         $columnName = $column->name;
         $pointsPossible = $column->pointsPossible;
      }
      
      $fieldset = new FieldSet ($this);
      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Column Name:', 'columnName', 'first');
      new TextInput ($div, 'columnName', 'columnName', $columnName);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Points Possible:', 'pointsPossible', 'first');
      new TextInput ($div, 'pointsPossible', 'pointsPossible', $pointsPossible);
       
      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');

      new HiddenField ($this, 'courseID', NULL, $course->courseID);
      
      if (! empty ($column)) {
         new HiddenField ($this, 'columnID', NULL, $column->columnID);
      }
   }
}


?>
