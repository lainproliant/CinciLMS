<?php
include_once "DAO/AssignmentDAO.php";

class AssignmentsVO {
   var $assignmentID, $typeID, $pointsPossible, $dueDate;
   
   public static function byAssignmentID ($assignmentID) {
      $dao = new AssignmentsDAO ();
      return self::fromResult ($dao->byAssignmentID ($assignmentID));
   }
   
   public static function fromResult ($result) {
      if (empty ($result)) {
         return NULL;
      }
      $obj = new static ();
      $obj->assignmentID = $result ["AssignmentID"];
      $obj->typeID = $result ["TypeID"];
      $obj->pointsPossible = $result ["PointsPossible"];
      $obj->dueDate = $result ["DueDate"];
      return $obj;
   }
   
   public static function listByAssignmentID ($assignmentID) {
      $dao = new AssignmentsDAO ();
      $objs = array ();
      foreach ($dao->listByAssignmentID ($assignmentID) as $result) {
         $objs [] = self::fromResult ($result);
      }
      return $objs;
   }
   
   protected function toData () {
      $data = array ();
      $data ["AssignmentID"] = $this->assignmentID;
      $data ["TypeID"] = $this->typeID;
      $data ["PointsPossible"] = $this->pointsPossible;
      $data ["DueDate"] = $this->dueDate;
      return $data;
   }
   
   public function insert () {
      $dao = new AssignmentsDAO ();
      $dao->insert ($this->toData ());
   }
   
   public function save () {
      $dao = new AssignmentsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new AssignmentsDAO ();
      $dao->delete ($this->assignmentID);
   }
}

class AssignmentFileSubmissionsVO {
   var $submissionID, $assignmentID, $studentID, $courseID, $submissionDate, $fileID;
   
   public static function bySubmissionID ($submissionID) {
      $dao = new AssignmentFileSubmissionsDAO ();
      return self::fromResult ($dao->bySubmissionID ($submissionID));
   }
   
   public static function fromResult ($result) {
      if (empty ($result)) {
         return NULL;
      }
      $obj = new static ();
      $obj->submissionID = $result ["SubmissionID"];
      $obj->assignmentID = $result ["AssignmentID"];
      $obj->studentID = $result ["StudentID"];
      $obj->courseID = $result ["CourseID"];
      $obj->submissionDate = $result ["SubmissionDate"];
      $obj->fileID = $result ["FileID"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["SubmissionID"] = $this->submissionID;
      $data ["AssignmentID"] = $this->assignmentID;
      $data ["StudentID"] = $this->studentID;
      $data ["CourseID"] = $this->courseID;
      $data ["SubmissionDate"] = $this->submissionDate;
      $data ["FileID"] = $this->fileID;
      return $data;
   }
   
   public function insert () {
      $dao = new AssignmentFileSubmissionsDAO ();
      $this->submissionID = $dao->insert ($this->toData ());
   }
   
   public function save () {
      $dao = new AssignmentFileSubmissionsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new AssignmentFileSubmissionsDAO ();
      $dao->delete ($this->submissionID);
   }
}

class AssignmentTypesVO {
   var $assignmentTypeID, $typeName;
   
   public static function byAssignmentTypeID ($assignmentTypeID) {
      $dao = new AssignmentTypesDAO ();
      return self::fromResult ($dao->byAssignmentTypeID ($assignmentTypeID));
   }
   
   public static function fromResult ($result) {
      if (empty ($result)) {
         return NULL;
      }
      $obj = new static ();
      $obj->assignmentTypeID = $result ["AssignmentTypeID"];
      $obj->typeName = $result ["TypeName"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["AssignmentTypeID"] = $this->assignmentTypeID;
      $data ["TypeName"] = $this->typeName;
      return $data;
   }
   
   public function insert () {
      $dao = new AssignmentTypesDAO ();
      $dao->insert ($this->toData ());
   }
   
   public function save () {
      $dao = new AssignmentTypesDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new AssignmentTypesDAO ();
      $dao->delete ($this->assignmentTypeID);
   }
}

?>
