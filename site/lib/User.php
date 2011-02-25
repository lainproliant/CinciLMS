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
}

