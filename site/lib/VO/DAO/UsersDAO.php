<?php
include_once "DAOException.php";

class UsersDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byUserID ($userID) {
      $stmt = $this->db->prepare ("select UserID, ExternalID, Username, FirstName, MiddleInitial, LastName, EmailAddress, PasswordSalt, PasswordHash, Notes, LastLogin, IsActive, SystemRole from Users where UserID = ?");
      $stmt->bind_param ("i", $userID);
      $stmt->execute ();
      $stmt->bind_result ($results ["UserID"], $results ["ExternalID"], $results ["Username"], $results ["FirstName"], $results ["MiddleInitial"], $results ["LastName"], $results ["EmailAddress"], $results ["PasswordSalt"], $results ["PasswordHash"], $results ["Notes"], $results ["LastLogin"], $results ["IsActive"], $results ["SystemRole"]);
      $stmt->fetch ();
      $stmt->close ();
      return $results;
   }
   
   public function byUsername ($username) {
      $results = array ();
      $stmt = $this->db->prepare ("select UserID, ExternalID, Username, FirstName, MiddleInitial, LastName, EmailAddress, PasswordSalt, PasswordHash, Notes, LastLogin, IsActive, SystemRole from Users where Username = ?");
      $stmt->bind_param ("s", $username);
      $stmt->execute ();
      $stmt->bind_result ($results ["UserID"], $results ["ExternalID"], $results ["Username"], $results ["FirstName"], $results ["MiddleInitial"], $results ["LastName"], $results ["EmailAddress"], $results ["PasswordSalt"], $results ["PasswordHash"], $results ["Notes"], $results ["LastLogin"], $results ["IsActive"], $results ["SystemRole"]);
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
      $query = sprintf ("select UserID, ExternalID, Username, FirstName, MiddleInitial, LastName, EmailAddress, PasswordSalt, PasswordHash, Notes, LastLogin, IsActive, SystemRole from Users where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->bind_result ($results ["UserID"], $results ["ExternalID"], $results ["Username"], $results ["FirstName"], $results ["MiddleInitial"], $results ["LastName"], $results ["EmailAddress"], $results ["PasswordSalt"], $results ["PasswordHash"], $results ["Notes"], $results ["LastLogin"], $results ["IsActive"], $results ["SystemRole"]);
      while ($stmt->fetch ()) {
         $resultsList [] = $results;
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into Users (ExternalID, Username, FirstName, MiddleInitial, LastName, EmailAddress, PasswordSalt, PasswordHash, Notes, LastLogin, IsActive, SystemRole) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param ("isssssssssii", $data ["ExternalID"], $data ["Username"], $data ["FirstName"], $data ["MiddleInitial"], $data ["LastName"], $data ["EmailAddress"], $data ["PasswordSalt"], $data ["PasswordHash"], $data ["Notes"], $data ["LastLogin"], $data ["IsActive"], $data ["SystemRole"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"Users\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update Users set ExternalID = ?, Username = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, EmailAddress = ?, PasswordSalt = ?, PasswordHash = ?, Notes = ?, LastLogin = ?, IsActive = ?, SystemRole = ? where UserID = ?");
      $stmt->bind_param ("isssssssssiii", $data ["ExternalID"], $data ["Username"], $data ["FirstName"], $data ["MiddleInitial"], $data ["LastName"], $data ["EmailAddress"], $data ["PasswordSalt"], $data ["PasswordHash"], $data ["Notes"], $data ["LastLogin"], $data ["IsActive"], $data ["SystemRole"], $data ["UserID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"Users\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($userID) {
      $stmt = $this->db->prepare ("delete from Users where UserID = ?");
      $stmt->bind_param ("i", $userID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"Users\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

?>
