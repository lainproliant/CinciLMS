<?php
include_once "DAO/UsersDAO.php";

class UsersVO {
   var $userID, $externalID, $username, $firstName, $middleInitial, $lastName, $emailAddress, $passwordSalt, $passwordHash, $notes, $lastLogin, $isActive, $systemRole;
   
   public static function byUserID ($userID) {
      $dao = new UsersDAO ();
      return self::fromResult ($dao->byUserID ($userID));
   }
   
   public static function byUsername ($username) {
      $dao = new UsersDAO ();
      return self::fromResult ($dao->byUsername ($username));
   }
   
   public static function fromResult ($result) {
      $obj = new static ();
      $obj->userID = $result ["UserID"];
      $obj->externalID = $result ["ExternalID"];
      $obj->username = $result ["Username"];
      $obj->firstName = $result ["FirstName"];
      $obj->middleInitial = $result ["MiddleInitial"];
      $obj->lastName = $result ["LastName"];
      $obj->emailAddress = $result ["EmailAddress"];
      $obj->passwordSalt = $result ["PasswordSalt"];
      $obj->passwordHash = $result ["PasswordHash"];
      $obj->notes = $result ["Notes"];
      $obj->lastLogin = $result ["LastLogin"];
      $obj->isActive = $result ["IsActive"];
      $obj->systemRole = $result ["SystemRole"];
      return $obj;
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
