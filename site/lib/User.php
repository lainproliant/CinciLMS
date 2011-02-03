<?php

/*
 * User: A class representation of a user in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "UserDAO.php";

class User {
   /*
    * Constructor for the User class.
    */
   function __construct ($userData = NULL) 
   {
      $this->UserID = 0;
      $this->ExternalID = 0;
      $this->Username = "";
      $this->FirstName = "";
      $this->LastName = "";
      $this->EmailAddress = "";
      $this->PasswordSalt = "";
      $this->PasswordHash = "";
      $this->Notes = "";
      $this->LastLogin = NULL;
      $this->IsActive = FALSE;
      $this->SystemRole = 0;

      if (! empty ($userData)) {
         $this->fromData ($userData);
      }
   }

   /*
    * Retrieves a user from the DAO via UserID.
    *
    * UserID:     The UserID of the user to retrieve.
    *
    * Raises UsernameException if the user was not found.
    */
   public static function byUserID ($UserID)
   {
      $DAO = new UserDAO ();
      return new User ($DAO->byUserID ($UserID));
   }

   /*
    * Retrieves a user from the DAO via ExternalID.
    *
    * ExternalID:     The ExternalID of the user to retrieve.
    *
    * Raises UsernameException if the user was not found.
    */
   public static function byExternalID ($ExternalID)
   {
      $DAO = new UserDAO ();
      return new User ($DAO->byExternalID ($ExternalID));
   }

   /*
    * Retrieves a user from the DAO via Username.
    *
    * Username:     The Username of the user to retrieve.
    *
    * Raises UsernameException if the user was not found.
    */
   public static function byUsername ($Username)
   {
      $DAO = new UserDAO ();
      return new User ($DAO->byUsername ($Username));
   }

   /*
    * Checks the given password against the user's password.
    * The password should first be salted against the server-salt.
    */
   public function checkPassword ($password)
   {
      return $this->PasswordHash == hash ('sha256', $this->PasswordSalt . $password);
   }

   /*
    * Changes the given password and saves it back to the DAO.
    *
    * password:   The new password.
    */
   public function changePassword ($password)
   {
      $DAO = new UserDAO ();

      $this->PasswordHash = hash ('sha256', $this->PasswordSalt . $password);
      $DAO->save ($this);
   }

   /*
    * PRIVATE
    * Constructs a User object from userData returned from the DAO.
    */
   protected function fromData ($userData)
   {
      $this->UserID              = $userData ['UserID'];
      $this->ExternalID          = $userData ['ExternalID'];
      $this->Username            = $userData ['Username'];
      $this->FirstName           = $userData ['FirstName'];
      $this->LastName            = $userData ['LastName'];
      $this->EmailAddress        = $userData ['EmailAddress'];
      $this->PasswordSalt        = $userData ['PasswordSalt'];
      $this->PasswordHash        = $userData ['PasswordHash'];
      $this->Notes               = $userData ['Notes'];
      $this->LastLogin           = strtotime ($userData ['LastLogin']);
      $this->IsActive            = $userData ['IsActive'];
      $this->SystemRole          = $userData ['SystemRole'];
   }

   /*
    * PRIVATE
    * Creates userData for the DAO object.
    */
   protected function toData ()
   {
      $userData = array ();

      $userData ['UserID']       = $this->UserID;
      $userData ['ExternalID']   = $this->ExternalID;
      $userData ['Username']     = $this->Username;
      $userData ['FirstName']    = $this->FirstName;
      $userData ['LastName']     = $this->LastName;
      $userData ['EmailAddress'] = $this->EmailAddress;
      $userData ['PasswordSalt'] = $this->PasswordSalt;
      $userData ['PasswordHash'] = $this->PasswordHash;
      $userData ['Notes']        = $this->Notes;
      $userData ['LastLogin']    = date('Y-m-d H:i:s', $this->LastLogin);
      $userData ['IsActive']     = $this->IsActive;
      $userData ['SystemRole']   = $this->SystemRole;

      return $userData;
   }
}

