<?php
include_once "DAO/CoursesDAO.php";

class CoursesVO {
   var $courseID, $courseName, $entryPointID, $accessFlags;
   
   public static function byCourseID ($courseID) {
      $dao = new CoursesDAO ();
      return self::fromResult ($dao->byCourseID ($courseID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->courseID = $result ["CourseID"];
      $obj->courseName = $result ["CourseName"];
      $obj->entryPointID = $result ["EntryPointID"];
      $obj->accessFlags = $result ["AccessFlags"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["CourseID"] = $this->courseID;
      $data ["CourseName"] = $this->courseName;
      $data ["EntryPointID"] = $this->entryPointID;
      $data ["AccessFlags"] = $this->accessFlags;
      return $data;
   }
   
   public function insert () {
      $dao = new CoursesDAO ();
      $this->courseID = $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new CoursesDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new CoursesDAO ();
      $dao->delete ($this->courseID);
   }
}

class FactCourseEnrollmentVO {
   var $userID, $courseID, $roleID, $accessFlags;
   
   public static function byUserID_CourseID ($userID, $courseID) {
      $dao = new FactCourseEnrollmentDAO ();
      return self::fromResult ($dao->byUserID_CourseID ($userID, $courseID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->userID = $result ["UserID"];
      $obj->courseID = $result ["CourseID"];
      $obj->roleID = $result ["RoleID"];
      $obj->accessFlags = $result ["AccessFlags"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["UserID"] = $this->userID;
      $data ["CourseID"] = $this->courseID;
      $data ["RoleID"] = $this->roleID;
      $data ["AccessFlags"] = $this->accessFlags;
      return $data;
   }
   
   public function insert () {
      $dao = new FactCourseEnrollmentDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new FactCourseEnrollmentDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new FactCourseEnrollmentDAO ();
      $dao->delete ($this->userID, $this->courseID);
   }
}

class CourseRolesVO {
   var $roleID, $roleName, $defaultAccess;
   
   public static function byRoleID ($roleID) {
      $dao = new CourseRolesDAO ();
      return self::fromResult ($dao->byRoleID ($roleID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->roleID = $result ["RoleID"];
      $obj->roleName = $result ["RoleName"];
      $obj->defaultAccess = $result ["DefaultAccess"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["RoleID"] = $this->roleID;
      $data ["RoleName"] = $this->roleName;
      $data ["DefaultAccess"] = $this->defaultAccess;
      return $data;
   }
   
   public function insert () {
      $dao = new CourseRolesDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new CourseRolesDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new CourseRolesDAO ();
      $dao->delete ($this->roleID);
   }
}

?>
