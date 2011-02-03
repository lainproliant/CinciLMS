<?php

/*
 * UserDAO: A data access object for users in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "Database.php";

class UserDAOException extends CinciDatabaseException { }
class UsernameException extends UserDAOException { }

class UserDAO {
   function __construct ($userData = NULL)
   {
      # Retrieve the database connection.
      $this->database = databaseConnect ();

      if (! empty ($userData)) {
         $this->fromData ($userData);
      }
   }

   /*
    * Retrieves userData by UserID.
    */
   public function byUserID ($UserID)
   {
      $userData = array ('UserID' => $UserID);

      $query = UserDAO::queryTemplate ("UserID = ?");
      $statement = $this->database->perpare ($query);

      $statement->bind_param ('i', $UserID);
      $statement->execute ();
      $statement->store_result ();
      if ($statement->num_rows < 1) {
         throw new UsernameException (sprintf ("No user matching the given UserID: %i", $UserID),
            $statement->error);
      }

      UserDAO::bindResult ($statement, $userData);
      $statement->fetch ();

      $statement->close ();

      return $userData;
   }

   /*
    * Retrieves userData by ExternalID.
    */
   public function byExternalID ($ExternalID)
   {
      $userData = array ();

      $query = UserDAO::queryTemplate ("ExternalID");
      $statement = $this->database->perpare ($query);

      $statement->bind_param ('i', $ExternalID);
      $statement->execute ();
      $statement->store_result ();
      if ($statement->num_rows < 1) {
         throw new UsernameException (sprintf ("No user matching the given ExternalID: %s", $ExternalID),
            $statement->error);
      }

      UserDAO::bindResult ($statement, $userData);
      $statement->fetch ();

      $statement->close ();

      return $userData;
   }

   /*
    * Retrieves userData by Username.
    */
   public function byUsername ($Username)
   {
      $userData = array ();

      $query = UserDAO::queryTemplate ("Username = ?");
      $statement = $this->database->prepare ($query);

      $statement->bind_param ('s', $Username);
      $statement->execute ();
      $statement->store_result ();
      if ($statement->num_rows < 1) {
         throw new UsernameException (sprintf ("No user matching the given Username: %s", $Username),
            $statement->error);
      }

      UserDAO::bindResult ($statement, $userData);
      $statement->fetch ();

      $statement->close ();

      return $userData;
   }

   /*
    * Updates existing user information in the database.
    */
   public function save ($user)
   {
      $query = "Update Users set
         ExternalID = ?,
         Username = ?,
         FirstName = ?,
         LastName = ?,
         EmailAddress = ?,
         PasswordSalt = ?,
         PasswordHash = ?,
         Notes = ?,
         LastLogin = ?,
         IsActive = ?,
         SystemRole = ?
         where UserID = ?";

      $statement = $this->database->prepare ($query);
      $statement->bind_param ('issssssssiii',
         $user->ExternalID,
         $user->Username,
         $user->FirstName,
         $user->LastName,
         $user->EmailAddress,
         $user->PasswordSalt,
         $user->PasswordHash,
         $user->Notes,
         $user->LastLogin,
         $user->IsActive,
         $user->SystemRole,
         $user->UserID);
      $statement->execute ();

      if ($statement->affected_rows != 1) {
         throw new UserDAOException (sprintf ("Couldn't update user records for \"%s\"", $user->Username),
            $statement->error);
      }
   }

   /*
    * PRIVATE
    * Returns a query for mysqli::prepare.
    *
    * NEVER PROVIDE USER INPUT TO THIS FUNCTION!
    * It is just a template provider for cleanlier code.
    */
   private static function queryTemplate ($param)
   {
      return sprintf ("select 
         UserID, 
         ExternalID,
         Username,
         FirstName,
         LastName,
         EmailAddress,
         PasswordSalt,
         PasswordHash,
         Notes,
         LastLogin,
         IsActive,
         SystemRole
         from Users where %s", $param);
   }

   /*
    * PRIVATE
    * Binds the results for a prepared statement to the associative array.
    */
   private static function bindResult ($statement, &$userData)
   {
      $statement->bind_result (
         $userData['UserID'], 
         $userData['ExternalID'],
         $userData['Username'],
         $userData['FirstName'],
         $userData['LastName'],
         $userData['EmailAddress'],
         $userData['PasswordSalt'],
         $userData['PasswordHash'],
         $userData['Notes'],
         $userData['LastLogin'],
         $userData['IsActive'],
         $userData['SystemRole']);
   }
}



