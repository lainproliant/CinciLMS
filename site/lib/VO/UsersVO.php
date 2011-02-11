<?php
include_once "DAO/UsersDAO.php";

class UsersVO {
   var $userID, $externalID, $username, $firstName, $middleInitial, $lastName, $emailAddress, $passwordSalt, $passwordHash, $notes, $lastLogin, $isActive, $systemRole;
   
   public function byUserID ($userID) {
      $dao = new UsersDAO ();
      $this->fromResult ($dao->byUserID ($userID));
   }
   
   public function byUsername ($username) {
      $dao = new UsersDAO ();
      $this->fromResult ($dao->byUsername ($username));
   }
   
   protected function fromResult ($result) {
      $this->userID = $result ["UserID"];
      $this->externalID = $result ["ExternalID"];
      $this->username = $result ["Username"];
      $this->firstName = $result ["FirstName"];
      $this->middleInitial = $result ["MiddleInitial"];
      $this->lastName = $result ["LastName"];
      $this->emailAddress = $result ["EmailAddress"];
      $this->passwordSalt = $result ["PasswordSalt"];
      $this->passwordHash = $result ["PasswordHash"];
      $this->notes = $result ["Notes"];
      $this->lastLogin = $result ["LastLogin"];
      $this->isActive = $result ["IsActive"];
      $this->systemRole = $result ["SystemRole"];
   }
   
   protected function toData () {
      $data = array ();
      $data ["UserID"] = $this->userID;
      $data ["ExternalID"] = $this->externalID;
      $data ["Username"] = $this->username;
      $data ["FirstName"] = $this->firstName;
      $data ["MiddleInitial"] = $this->middleInitial;
      $data ["LastName"] = $this->lastName;
      $data ["EmailAddress"] = $this->emailAddress;
      $data ["PasswordSalt"] = $this->passwordSalt;
      $data ["PasswordHash"] = $this->passwordHash;
      $data ["Notes"] = $this->notes;
      $data ["LastLogin"] = $this->lastLogin;
      $data ["IsActive"] = $this->isActive;
      $data ["SystemRole"] = $this->systemRole;
      return $data;
   }
   
   public function insert () {
      $dao = new UsersDAO ();
      $this->userID = $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new UsersDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new UsersDAO ();
      $dao->delete ($this->userID);
   }
}

?>
