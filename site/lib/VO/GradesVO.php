<?php
include_once "DAO/GradesDAO.php";

class GradeColumnsVO {
   var $columnID, $courseID, $name, $pointsPossible, $assignmentID, $sortOrder;
   
   public static function byColumnID ($columnID) {
      $dao = new GradeColumnsDAO ();
      return self::fromResult ($dao->byColumnID ($columnID));
   }
   
   public static function fromResult ($result) {
      $obj = new static ();
      $obj->columnID = $result ["ColumnID"];
      $obj->courseID = $result ["CourseID"];
      $obj->name = $result ["Name"];
      $obj->pointsPossible = $result ["PointsPossible"];
      $obj->assignmentID = $result ["AssignmentID"];
      $obj->sortOrder = $result ["SortOrder"];
      return $obj;
   }
   
   public static function listByCourseID ($courseID) {
      $dao = new GradeColumnsDAO ();
      $objs = array ();
      foreach ($dao->listByCourseID ($courseID) as $result) {
         $objs [] = self::fromResult ($result);
      }
      return $objs;
   }
   
   protected function toData () {
      $data = array ();
      $data ["ColumnID"] = $this->columnID;
      $data ["CourseID"] = $this->courseID;
      $data ["Name"] = $this->name;
      $data ["PointsPossible"] = $this->pointsPossible;
      $data ["AssignmentID"] = $this->assignmentID;
      $data ["SortOrder"] = $this->sortOrder;
      return $data;
   }
   
   public function insert () {
      $dao = new GradeColumnsDAO ();
      $this->columnID = $dao->insert ($this->toData ());
   }
   
   public function save () {
      $dao = new GradeColumnsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new GradeColumnsDAO ();
      $dao->delete ($this->columnID);
   }
}

class GradesVO {
   var $columnID, $studentID, $grade;
   
   public static function byColumnID_StudentID ($columnID, $studentID) {
      $dao = new GradesDAO ();
      return self::fromResult ($dao->byColumnID_StudentID ($columnID, $studentID));
   }
   
   public static function fromResult ($result) {
      $obj = new static ();
      $obj->columnID = $result ["ColumnID"];
      $obj->studentID = $result ["StudentID"];
      $obj->grade = $result ["Grade"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["ColumnID"] = $this->columnID;
      $data ["StudentID"] = $this->studentID;
      $data ["Grade"] = $this->grade;
      return $data;
   }
   
   public function insert () {
      $dao = new GradesDAO ();
      $dao->insert ($this->toData ());
   }
   
   public function save () {
      $dao = new GradesDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new GradesDAO ();
      $dao->delete ($this->columnID, $this->studentID);
   }
}

?>
