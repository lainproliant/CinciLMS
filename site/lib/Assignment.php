<?php

/*
 * Assignment: An implementation of a file-upload assignment.
 * (c) 2011 Lee Supe (lain_proliant)
 *
 * Released under the GNU General Public License, version 3.
 */

include_once "Content.php";
include_once "VO/AssignmentVO.php";
include_once "VO/DAO/AssignmentExtendedDAO.php";

define ('ASSIGNMENT_TYPE_PROJECT', 1);

class Assignment extends ContentItem {

   function __construct ($contentItem = NULL) {
      parent::__construct ($contentItem);

      $this->typeID = CONTENT_TYPE_ASSIGNMENT;

      $this->assignmentTypeID = ASSIGNMENT_TYPE_PROJECT;
      $this->pointsPossible = 100;
      $this->dueDate = NULL;
   }

   /*
    * Fetches the assignment info.
    */
   public function getAssignmentItemInfo ()
   {
      $assignmentInfo = AssignmentsVO::byAssignmentID ($this->contentID);
      return $assignmentInfo;
   }

   protected function createVO ()
   {
      $vo = new AssignmentsVO ();
      
      printf ("(LRS-DEBUG A: %d)", $this->contentID);

      $vo->assignmentID = $this->contentID;
      $vo->typeID = $this->assignmentTypeID;
      $vo->pointsPossible = $this->pointsPossible;
      $vo->dueDate = $this->dueDate;

      return array_merge (parent::createVO (), array ($vo));
   }

   /*
    * Creates a grade column for the assignment in the given course.
    *
    * Throws CinciException if we can't get sufficient permissions
    * to create a column in the grade record of the given course.
    */
   public function createGradeColumn ($authority, $user, $course)
   {
      $column = new GradeColumn ();

      $column->courseID = $course->courseID;
      $column->name = $this->name;
      $column->pointsPossible = $this->pointsPossible;
      $column->assignmentID = $this->contentID;

      $enrollment = $course->getEnrollment ($user);

      printf ("(LRS-DEBUG: %d)", $this->contentID);

      if ($course->checkWriteGradesAbility ($authority, $user, $enrollment)) {

         $column->insert ();

      } else {
         throw new CinciException ("Assignment Grade Column Error",
            "You do not have permission to make changes to the grade record of this course.");
      }
   }

   /*
    * Displays the course content in the provided Div.
    * This isn't needed for assignments yet.
    */
   public function display ($contentDiv, $path, $authority, $user,
      $course, $enrollment) { }

   /*
    * Displays an iconified representation of the content in
    * the provided Div.
    */
   public function displayItem ($contentDiv, $path, $authority, $user,
      $course, $enrollment)
   {
      $itemInfo = $this->getContentItemInfo ();
   
      $div = new Div ($contentDiv, 'content-item item');
      
      $header = new XMLEntity ($div, 'h4');
      
      new TextEntity ($header, $itemInfo->title);
      
      new TextEntity ($div, $itemInfo->text); 

      new Br ($div);
      
      $uploadForm = new UploadForm ($div, sprintf ("?action=uploadAssignment&assignmentIdentity=%d:%d",
         $course->courseID, $this->contentID), "Upload Assignment: ");

      new SubmitButton ($uploadForm, "Submit");
   }
}

class AssignmentSubmissions extends AssignmentFileSubmissionsVO {
   public static function listByCourseID_StudentID_AssignmentID ($courseID, $studentID, $assignmentID)
   {
      $dao = new AssignmentSubmissionsExtendedDAO ();
      $submissions = array ();

      $lambda = create_function ('$a', 'return $a;');

      $results = $dao->listByCourseID_StudentID_AssignmentID (
         $courseID, $studentID, $assignmentID);

      foreach ($results as $result) {
         $submissions [] = $lambda (self::fromResult ($result));
      }

      return $submissions;
   }
}
