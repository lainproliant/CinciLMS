<?php

/*
 * User: A class representation of a user in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "CourseJoin.php";
include_once "VO/UsersVO.php";

class User extends UsersVO {
   /*
    * Checks the given password against the user's password.
    * The password should first be salted against the server-salt.
    */
   public function checkPassword ($password)
   {
      return $this->passwordHash == hash ('sha256', $this->passwordSalt . $password);
   }

   /* 
    * A convenience method.  Changes the user's password to the 
    * given server-salted password and saves it back to the database.
    */
   public function changePassword ($password)
   {
      $this->setPassword ($password);
      $this->save ();
   }
   
   /* 
    * Changes the user's password to the given server-salted password
    * without saving.
    */
   public function setPassword ($password)
   {
      $this->passwordHash = hash ('sha256', $this->passwordSalt . $password);
   }

   /*
    * Generates the per-user salt for the given user.  This method
    * should be called on new User objects before insertion.
    */
   public function generateSalt ()
   {
      $this->passwordSalt = hash ('sha256', openssl_random_pseudo_bytes (64, $strong_crypto));
   }

   /*
    * Gets a list of the enrollments and courses for this user.
    */
   public function getCourseEnrollments ()
   {
      return CourseJoin::joinUserID_CourseEnrollment (
         $this->userID);
   }

   /*
    * Search for users with usernames that match or begin with the
    * given characters.
    */
   public static function searchByUsername ($username)
   {
      $dao = new UsersDAO ();
      $users = array ();

      $lambda = create_function ('$a', 'return $a;');

      $results = $dao->search (array ("Username like ?" =>
         sprintf ("%s%%", $username)));

      foreach ($results as $result) {
         $users [] = $lambda (self::fromResult ($result));
      }

      return $users;
   }


   /*
    * Search for users with last names that match or begin with the
    * given characters.
    */
   public static function searchByLastName ($lastName)
   {
      $dao = new UsersDAO ();
      $users = array ();

      $lambda = create_function ('$a', 'return $a;');

      $results = $dao->search (array ("LastName like ?" =>
         sprintf ("%s%%", $lastName)));

      foreach ($results as $result) {
         $users [] = $lambda (self::fromResult ($result));
      }

      return $users;
   }


   /*
    * Search for users with last and first names that match or begin
    * with the given characters.  The last name must match if
    * searching with a first name.
    *
    * fullName: The full name to search with.  It should be in the form
    *           of "Last, First".
    */
   public static function searchByFullName ($fullName)
   {
      $dao = new UsersDAO ();
      $users = array ();

      list ($lastName, $firstName) = split (", ", $fullName);
      
      $lastName = trim ($lastName);
      $firstName = trim ($firstName);

      $lambda = create_function ('$a', 'return $a;');

      $results = $dao->search (array (
         "LastName like ?" => sprintf ("%s%%", $lastName),
         "FirstName like ?" => sprintf ("%s%%", $firstName)));

      foreach ($results as $result) {
         $users [] = $lambda (self::fromResult ($result));
      }

      return $users;
   }

   /*
    * Search for the currently logged in user's user object.
    * If there is no user currently logged in, return NULL.
    */
   public static function getCurrentUser ()
   {
      $user = NULL;

      if (! empty ($_SESSION ['userid'])) {
         $user = User::byUserID ($_SESSION ['userid']);
      }

      return $user;
   }

   /*
    * A convenience method to get the current username
    * of the currently logged in user.
    *
    * Returns "guest" if there is no user logged in.
    */
   public static function getCurrentUsername ()
   {
      $user = static::getCurrentUser ();

      if (empty ($user)) {
         return "guest";
      } else {
         return $user->username;
      }
   }
}

?>
