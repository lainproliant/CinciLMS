<?php
include_once "DAOException.php";

class AssignmentsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byAssignmentID ($assignmentID) {
      $stmt = $this->db->prepare ("select AssignmentID, TypeID, PointsPossible, DueDate from Assignments where AssignmentID = ?");
      $stmt->bind_param ("i", $assignmentID);
      $stmt->execute ();
      $stmt->bind_result ($results ["AssignmentID"], $results ["TypeID"], $results ["PointsPossible"], $results ["DueDate"]);
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
   $query = "select AssignmentID, TypeID, PointsPossible, DueDate from Assignments";
   $stmt = $this->db->prepare ($query);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["AssignmentID"], $results ["TypeID"], $results ["PointsPossible"], $results ["DueDate"]);
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
   $query = sprintf ("select AssignmentID, TypeID, PointsPossible, DueDate from Assignments where %s", implode (" and ", array_keys ($params)));
   $query .= ' ' . $postfix;
   $bindParamArgs = array (&$bindTypes);
   foreach ($params as $key => $value) {
      $bindParamArgs [$key] = &$params [$key];
   }
   $stmt = $this->db->prepare ($query);
   call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["AssignmentID"], $results ["TypeID"], $results ["PointsPossible"], $results ["DueDate"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function insert ($data) {
   $stmt = $this->db->prepare ("insert into Assignments (TypeID, PointsPossible, DueDate) values (?, ?, ?)");
   $stmt->bind_param ("iis", $data ["TypeID"], $data ["PointsPossible"], $data ["DueDate"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't insert record in the table \"Assignments\"", $stmt->error, $stmt->affected_rows);
   }
   return $this->db->insert_id;
}

public function save ($data) {
   $stmt = $this->db->prepare ("update Assignments set TypeID = ?, PointsPossible = ?, DueDate = ? where AssignmentID = ?");
   $stmt->bind_param ("iisi", $data ["TypeID"], $data ["PointsPossible"], $data ["DueDate"], $data ["AssignmentID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't save record in table \"Assignments\"", $stmt->error, $stmt->affected_rows);
   }
}

public function delete ($assignmentID) {
   $stmt = $this->db->prepare ("delete from Assignments where AssignmentID = ?");
   $stmt->bind_param ("i", $assignmentID);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Problem deleting record from table \"Assignments\"", $stmt->error, $stmt->affected_rows);
   }
}
}

class AssignmentFileSubmissionsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function bySubmissionID ($submissionID) {
      $stmt = $this->db->prepare ("select SubmissionID, AssignmentID, StudentID, CourseID, SubmissionDate, FileID from AssignmentFileSubmissions where SubmissionID = ?");
      $stmt->bind_param ("i", $submissionID);
      $stmt->execute ();
      $stmt->bind_result ($results ["SubmissionID"], $results ["AssignmentID"], $results ["StudentID"], $results ["CourseID"], $results ["SubmissionDate"], $results ["FileID"]);
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
   $query = "select SubmissionID, AssignmentID, StudentID, CourseID, SubmissionDate, FileID from AssignmentFileSubmissions";
   $stmt = $this->db->prepare ($query);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["SubmissionID"], $results ["AssignmentID"], $results ["StudentID"], $results ["CourseID"], $results ["SubmissionDate"], $results ["FileID"]);
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
   $query = sprintf ("select SubmissionID, AssignmentID, StudentID, CourseID, SubmissionDate, FileID from AssignmentFileSubmissions where %s", implode (" and ", array_keys ($params)));
   $query .= ' ' . $postfix;
   $bindParamArgs = array (&$bindTypes);
   foreach ($params as $key => $value) {
      $bindParamArgs [$key] = &$params [$key];
   }
   $stmt = $this->db->prepare ($query);
   call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["SubmissionID"], $results ["AssignmentID"], $results ["StudentID"], $results ["CourseID"], $results ["SubmissionDate"], $results ["FileID"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function insert ($data) {
   $stmt = $this->db->prepare ("insert into AssignmentFileSubmissions (AssignmentID, StudentID, CourseID, SubmissionDate, FileID) values (?, ?, ?, ?, ?)");
   $stmt->bind_param ("iiisi", $data ["AssignmentID"], $data ["StudentID"], $data ["CourseID"], $data ["SubmissionDate"], $data ["FileID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't insert record in the table \"AssignmentFileSubmissions\"", $stmt->error, $stmt->affected_rows);
   }
   return $this->db->insert_id;
}

public function save ($data) {
   $stmt = $this->db->prepare ("update AssignmentFileSubmissions set AssignmentID = ?, StudentID = ?, CourseID = ?, SubmissionDate = ?, FileID = ? where SubmissionID = ?");
   $stmt->bind_param ("iiisii", $data ["AssignmentID"], $data ["StudentID"], $data ["CourseID"], $data ["SubmissionDate"], $data ["FileID"], $data ["SubmissionID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't save record in table \"AssignmentFileSubmissions\"", $stmt->error, $stmt->affected_rows);
   }
}

public function delete ($submissionID) {
   $stmt = $this->db->prepare ("delete from AssignmentFileSubmissions where SubmissionID = ?");
   $stmt->bind_param ("i", $submissionID);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Problem deleting record from table \"AssignmentFileSubmissions\"", $stmt->error, $stmt->affected_rows);
   }
}
}

class AssignmentTypesDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byAssignmentTypeID ($assignmentTypeID) {
      $stmt = $this->db->prepare ("select AssignmentTypeID, TypeName from AssignmentTypes where AssignmentTypeID = ?");
      $stmt->bind_param ("i", $assignmentTypeID);
      $stmt->execute ();
      $stmt->bind_result ($results ["AssignmentTypeID"], $results ["TypeName"]);
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
   $query = "select AssignmentTypeID, TypeName from AssignmentTypes";
   $stmt = $this->db->prepare ($query);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["AssignmentTypeID"], $results ["TypeName"]);
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
   $query = sprintf ("select AssignmentTypeID, TypeName from AssignmentTypes where %s", implode (" and ", array_keys ($params)));
   $query .= ' ' . $postfix;
   $bindParamArgs = array (&$bindTypes);
   foreach ($params as $key => $value) {
      $bindParamArgs [$key] = &$params [$key];
   }
   $stmt = $this->db->prepare ($query);
   call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
   $stmt->execute ();
   $lambda = create_function ('$a', 'return $a;');
   $stmt->bind_result ($results ["AssignmentTypeID"], $results ["TypeName"]);
   while ($stmt->fetch ()) {
      $resultsList [] = array_map ($lambda, $results);
   }
   $stmt->close ();
   return $resultsList;
}

public function insert ($data) {
   $stmt = $this->db->prepare ("insert into AssignmentTypes (AssignmentTypeID, TypeName) values (?, ?)");
   $stmt->bind_param ("is", $data ["AssignmentTypeID"], $data ["TypeName"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't insert record in the table \"AssignmentTypes\"", $stmt->error, $stmt->affected_rows);
   }
   return $this->db->insert_id;
}

public function save ($data) {
   $stmt = $this->db->prepare ("update AssignmentTypes set TypeName = ? where AssignmentTypeID = ?");
   $stmt->bind_param ("si", $data ["TypeName"], $data ["AssignmentTypeID"]);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Couldn't save record in table \"AssignmentTypes\"", $stmt->error, $stmt->affected_rows);
   }
}

public function delete ($assignmentTypeID) {
   $stmt = $this->db->prepare ("delete from AssignmentTypes where AssignmentTypeID = ?");
   $stmt->bind_param ("i", $assignmentTypeID);
   $stmt->execute ();
   if ($stmt->affected_rows != 1) {
      throw new DAOException ("Problem deleting record from table \"AssignmentTypes\"", $stmt->error, $stmt->affected_rows);
   }
}
}

?>
