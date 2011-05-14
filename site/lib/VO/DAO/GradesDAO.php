<?php
include_once "DAOException.php";

class GradeColumnsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byColumnID ($columnID) {
      $stmt = $this->db->prepare ("select ColumnID, CourseID, Name, PointsPossible, AssignmentID, SortOrder from GradeColumns where ColumnID = ?");
      $stmt->bind_param ("i", $columnID);
      $stmt->execute ();
      $stmt->bind_result ($results ["ColumnID"], $results ["CourseID"], $results ["Name"], $results ["PointsPossible"], $results ["AssignmentID"], $results ["SortOrder"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 0) {
         $results = NULL;
      }
   $stmt->close ();
   return $results;
}

public function listByCourseID ($courseID) {
   $resultsList = array ();
   $results = array ();
   $stmt = $this->db->prepare ("select ColumnID, CourseID, Name, PointsPossible, AssignmentID, SortOrder from GradeColumns where CourseID = ?");
   $stmt->bind_param ("i", $courseID);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["ColumnID"], $results ["CourseID"], $results ["Name"], $results ["PointsPossible"], $results ["AssignmentID"], $results ["SortOrder"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function fetchAll () {
   $resultsList = array ();
   $results = array ();
   $query = "select ColumnID, CourseID, Name, PointsPossible, AssignmentID, SortOrder from GradeColumns";
   $stmt = $this->db->prepare ($query);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["ColumnID"], $results ["CourseID"], $results ["Name"], $results ["PointsPossible"], $results ["AssignmentID"], $results ["SortOrder"]);
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
   $query = sprintf ("select ColumnID, CourseID, Name, PointsPossible, AssignmentID, SortOrder from GradeColumns where %s", implode (" and ", array_keys ($params)));
   $query .= ' ' . $postfix;
   $bindParamArgs = array (&$bindTypes);
   foreach ($params as $key => $value) {
      $bindParamArgs [$key] = &$params [$key];
   }
   $stmt = $this->db->prepare ($query);
   call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["ColumnID"], $results ["CourseID"], $results ["Name"], $results ["PointsPossible"], $results ["AssignmentID"], $results ["SortOrder"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function insert ($data) {
   $stmt = $this->db->prepare ("insert into GradeColumns (CourseID, Name, PointsPossible, AssignmentID, SortOrder) values (?, ?, ?, ?, ?)");
   $stmt->bind_param ("isiii", $data ["CourseID"], $data ["Name"], $data ["PointsPossible"], $data ["AssignmentID"], $data ["SortOrder"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't insert record in the table \"GradeColumns\"", $stmt->error, $stmt->affected_rows);
   }
   return $this->db->insert_id;
}

public function save ($data) {
   $stmt = $this->db->prepare ("update GradeColumns set CourseID = ?, Name = ?, PointsPossible = ?, AssignmentID = ?, SortOrder = ? where ColumnID = ?");
   $stmt->bind_param ("isiiii", $data ["CourseID"], $data ["Name"], $data ["PointsPossible"], $data ["AssignmentID"], $data ["SortOrder"], $data ["ColumnID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't save record in table \"GradeColumns\"", $stmt->error, $stmt->affected_rows);
   }
}

public function delete ($columnID) {
   $stmt = $this->db->prepare ("delete from GradeColumns where ColumnID = ?");
   $stmt->bind_param ("i", $columnID);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Problem deleting record from table \"GradeColumns\"", $stmt->error, $stmt->affected_rows);
   }
}
}

class GradesDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byColumnID_StudentID ($columnID, $studentID) {
      $stmt = $this->db->prepare ("select ColumnID, StudentID, Grade from Grades where ColumnID = ? and StudentID = ?");
      $stmt->bind_param ("ii", $columnID, $studentID);
      $stmt->execute ();
      $stmt->bind_result ($results ["ColumnID"], $results ["StudentID"], $results ["Grade"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 0) {
         $results = NULL;
      }
   $stmt->close ();
   return $results;
}

public function fetchAll () {
   $resultsList = array ();
   $results = array ();
   $query = "select ColumnID, StudentID, Grade from Grades";
   $stmt = $this->db->prepare ($query);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["ColumnID"], $results ["StudentID"], $results ["Grade"]);
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
   $query = sprintf ("select ColumnID, StudentID, Grade from Grades where %s", implode (" and ", array_keys ($params)));
   $query .= ' ' . $postfix;
   $bindParamArgs = array (&$bindTypes);
   foreach ($params as $key => $value) {
      $bindParamArgs [$key] = &$params [$key];
   }
   $stmt = $this->db->prepare ($query);
   call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["ColumnID"], $results ["StudentID"], $results ["Grade"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function insert ($data) {
   $stmt = $this->db->prepare ("insert into Grades (ColumnID, StudentID, Grade) values (?, ?, ?)");
   $stmt->bind_param ("iis", $data ["ColumnID"], $data ["StudentID"], $data ["Grade"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't insert record in the table \"Grades\"", $stmt->error, $stmt->affected_rows);
   }
   return $this->db->insert_id;
}

public function save ($data) {
   $stmt = $this->db->prepare ("update Grades set Grade = ? where ColumnID = ? and StudentID = ?");
   $stmt->bind_param ("sii", $data ["Grade"], $data ["ColumnID"], $data ["StudentID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't save record in table \"Grades\"", $stmt->error, $stmt->affected_rows);
   }
}

public function delete ($columnID, $studentID) {
   $stmt = $this->db->prepare ("delete from Grades where ColumnID = ? and StudentID = ?");
   $stmt->bind_param ("ii", $columnID, $studentID);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Problem deleting record from table \"Grades\"", $stmt->error, $stmt->affected_rows);
   }
}
}

?>
