<?php

/*
 * User: A class representation of a user in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

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
    * Changes the user's password to the given server-salted password
    * and saves it back to the database.
    */
   public function changePassword ($password)
   {
      $this->passwordHash = hash ('sha256', $this->passwordSalt . $password);
      $this->save ();
   }
}

