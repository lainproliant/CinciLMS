<?php
include_once "DAOException.php";

class CoursesDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byCourseID ($courseID) {
      $stmt = $this->db->prepare ("select CourseID, CourseCode, CourseName, EntryPointID, AccessFlags from Courses where CourseID = ?");
      $stmt->bind_param ("i", $courseID);
      $stmt->execute ();
      $stmt->bind_result ($results ["CourseID"], $results ["CourseCode"], $results ["CourseName"], $results ["EntryPointID"], $results ["AccessFlags"]);
      $stmt->fetch ();
      $stmt->close ();
      return $results;
   }
   
   public function byCourseCode ($courseCode) {
      $results = array ();
      $stmt = $this->db->prepare ("select CourseID, CourseCode, CourseName, EntryPointID, AccessFlags from Courses where CourseCode = ?");
      $stmt->bind_param ("s", $courseCode);
      $stmt->execute ();
      $stmt->bind_result ($results ["CourseID"], $results ["CourseCode"], $results ["CourseName"], $results ["EntryPointID"], $results ["AccessFlags"]);
      $stmt->fetch ();
      $stmt->close ();
      return $results;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select CourseID, CourseCode, CourseName, EntryPointID, AccessFlags from Courses where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["CourseID"], $results ["CourseCode"], $results ["CourseName"], $results ["EntryPointID"], $results ["AccessFlags"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into Courses (CourseCode, CourseName, EntryPointID, AccessFlags) values (?, ?, ?, ?)");
      $stmt->bind_param ("ssis", $data ["CourseCode"], $data ["CourseName"], $data ["EntryPointID"], $data ["AccessFlags"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"Courses\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update Courses set CourseCode = ?, CourseName = ?, EntryPointID = ?, AccessFlags = ? where CourseID = ?");
      $stmt->bind_param ("ssisi", $data ["CourseCode"], $data ["CourseName"], $data ["EntryPointID"], $data ["AccessFlags"], $data ["CourseID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"Courses\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($courseID) {
      $stmt = $this->db->prepare ("delete from Courses where CourseID = ?");
      $stmt->bind_param ("i", $courseID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"Courses\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class CourseRolesDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byRoleID ($roleID) {
      $stmt = $this->db->prepare ("select RoleID, RoleName, DefaultAccess from CourseRoles where RoleID = ?");
      $stmt->bind_param ("i", $roleID);
      $stmt->execute ();
      $stmt->bind_result ($results ["RoleID"], $results ["RoleName"], $results ["DefaultAccess"]);
      $stmt->fetch ();
      $stmt->close ();
      return $results;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select RoleID, RoleName, DefaultAccess from CourseRoles where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["RoleID"], $results ["RoleName"], $results ["DefaultAccess"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into CourseRoles (RoleID, RoleName, DefaultAccess) values (?, ?, ?)");
      $stmt->bind_param ("iss", $data ["RoleID"], $data ["RoleName"], $data ["DefaultAccess"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"CourseRoles\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update CourseRoles set RoleName = ?, DefaultAccess = ? where RoleID = ?");
      $stmt->bind_param ("ssi", $data ["RoleName"], $data ["DefaultAccess"], $data ["RoleID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"CourseRoles\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($roleID) {
      $stmt = $this->db->prepare ("delete from CourseRoles where RoleID = ?");
      $stmt->bind_param ("i", $roleID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"CourseRoles\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class FactCourseEnrollmentDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byUserID_CourseID ($userID, $courseID) {
      $stmt = $this->db->prepare ("select UserID, CourseID, RoleID, AccessFlags from FactCourseEnrollment where UserID = ? and CourseID = ?");
      $stmt->bind_param ("ii", $userID, $courseID);
      $stmt->execute ();
      $stmt->bind_result ($results ["UserID"], $results ["CourseID"], $results ["RoleID"], $results ["AccessFlags"]);
      $stmt->fetch ();
      $stmt->close ();
      return $results;
   }
   
   public function listByCourseID ($courseID) {
      $resultsList = array ();
      $results = array ();
      $stmt = $this->db->prepare ("select UserID, CourseID, RoleID, AccessFlags from FactCourseEnrollment where CourseID = ?");
      $stmt->bind_param ("i", $courseID);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["UserID"], $results ["CourseID"], $results ["RoleID"], $results ["AccessFlags"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select UserID, CourseID, RoleID, AccessFlags from FactCourseEnrollment where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["UserID"], $results ["CourseID"], $results ["RoleID"], $results ["AccessFlags"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into FactCourseEnrollment (UserID, CourseID, RoleID, AccessFlags) values (?, ?, ?, ?)");
      $stmt->bind_param ("iiis", $data ["UserID"], $data ["CourseID"], $data ["RoleID"], $data ["AccessFlags"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"FactCourseEnrollment\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update FactCourseEnrollment set RoleID = ?, AccessFlags = ? where UserID = ? and CourseID = ?");
      $stmt->bind_param ("isii", $data ["RoleID"], $data ["AccessFlags"], $data ["UserID"], $data ["CourseID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"FactCourseEnrollment\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($userID, $courseID) {
      $stmt = $this->db->prepare ("delete from FactCourseEnrollment where UserID = ? and CourseID = ?");
      $stmt->bind_param ("ii", $userID, $courseID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"FactCourseEnrollment\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

?>
