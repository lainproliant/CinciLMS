<?php

/*
 * CourseDAO: Data access objects for courses and related objects. 
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "Database.php";

define ("CONTENT_TYPE_ITEM",           1);
define ("CONTENT_TYPE_ASSIGNMENT",     2);
define ("CONTENT_TYPE_FOLDER",         3);
define ("CONTENT_TYPE_LINK",           4);

class CourseDAOException extends CinciDatabaseException { }
class CourseContentException extends CourseDAOException { }

class CourseContentDAO {
   function __construct ()
   {
      # Retrieve the database connection.
      $this->database = databaseConnect ();
   }

   /*
    * Retrieves a generic Content item by ContentID.
    */
   public function byContentID ($ContentID)
   {
      $contentData = array ();

      $query = CourseContentDAO::queryTemplate ("ContentID = ?");
      $statement = $this->database->perpare ($query);

      $statement->bind_param ('i', $ContentID);
      $statement->execute ();
      $statement->store_result ();
      if ($statement->num_rows < 1) {
         throw new UsernameException (sprintf ("No course content matching the given ContentID: %d",
            $ContentID),
         $statement->error);
      }
      CourseContentDAO::bindResult ($statement, $userData);
      $statement->fetch ();

      $statement->close ();

      return $userData;
   }

   /*
    * Updates an existing course content entry.
    */
   public function save ($content)
   {
      $query = "Update CourseContent set
         ParentID = ?,
         OwnerID = ?,
         TypeID = ?,
         Name = ?,
         AccessFlags = ?
         where ContentID = ?";

      $statement = $this->database->prepare ($query);

      $statement->bind_param ('iiissi'
         $content->ParentID,
         $content->OwnerID,
         $content->TypeID,
         $content->Name,
         $content->AccessFlags,
         $content->ContentID);
      $statement->execute ();

      if ($statement->affected_rows != 1) {
         throw new ContentDAOException (sprintf ("Couldn't update course content for ID %d", 
            $content->ContentID),
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
         ParentID,
         OwnerID,
         TypeID,
         Name,
         AccessFlags
         from CourseContent where %s", $param);
   }

   /*
    * PRIVATE
    * Binds the results for a prepared statement to the associative array.
    */
   private static function bindResult ($statement, &$contentData)
   {
      $statement->bind_result (
         $userData['ParentID'],
         $userData['OwnerID'],
         $userData['TypeID'],
         $userData['Name'],
         $userData['AccessFlags']);
   }
}

class ContentItemDAO extends CourseContentDAO {
   function __construct ()
   {
      parent::__construct ();
   }

   public function byLinkID ($LinkID)
   {
      $linkData = array ('ContentID' => $LinkID);

      $query = ContentLinkDAO::queryTemplate ("LinkID = ?");
      $statement = $this->database->prepare ($query);

      $statement->bind_param ('i', $LinkID);
      $statement->execute ();
      $statement->store_result ();
      
      if ($statement->num_rows < 1) {
         throw new CourseContentException (sprintf ("No content link matching the given ContentID: %d",
            $LinkID),
         $statement->error);
      }

      ContentLinkDAO::bindResult ($statement, $linkData);
      $statement->fetch ();

      $statement->close ();

      return $linkData;
   }
   
   public function save ($link)
   {
      $query = "Update ContentLinks set
         DestinationID = ?
         where LinkID = ?";

      $statement= $this->database->prepare ($query);

      $statement->bind_param ('ii',
         $link->DestinationID,
         $link->ContentID
      );
      
      $statement->execute ();

      if ($statement->affected_rows != 1) {
         throw new ContentDAOException (sprintf ("Couldn't update content link for ID %d", 
            $link->ContentID),
         $statement->error);
      }

   }

   private static function queryTemplate ($param)
   {
      return sprintf ("select
         DestinationID
         from ContentLinks where %s",
         $param);
   }

   private static function bindResult ($statement, &$linkData)
   {
      $statement->bind_result (
         $linkData ['DestinationID']);
   }
}

class ContentLinkDAO extends CourseContentDAO {
   function __construct ()
   {
      parent::__construct ();
   }

   public function byLinkID ($LinkID)
   {
      $linkData = array ('ContentID' => $LinkID);

      $query = ContentLinkDAO::queryTemplate ("LinkID = ?");
      $statement = $this->database->prepare ($query);

      $statement->bind_param ('i', $LinkID);
      $statement->execute ();
      $statement->store_result ();
      
      if ($statement->num_rows < 1) {
         throw new CourseContentException (sprintf ("No content link matching the given ContentID: %d",
            $LinkID),
         $statement->error);
      }

      ContentLinkDAO::bindResult ($statement, $linkData);
      $statement->fetch ();

      $statement->close ();

      return $linkData;
   }
   
   public function save ($link)
   {
      $query = "Update ContentLinks set
         DestinationID = ?
         where LinkID = ?";

      $statement= $this->database->prepare ($query);

      $statement->bind_param ('ii',
         $link->DestinationID,
         $link->ContentID
      );
      
      $statement->execute ();

      if ($statement->affected_rows != 1) {
         throw new ContentDAOException (sprintf ("Couldn't update content link for ID %d", 
            $link->ContentID),
         $statement->error);
      }

   }

   private static function queryTemplate ($param)
   {
      return sprintf ("select
         DestinationID
         from ContentLinks where %s",
         $param);
   }

   private static function bindResult ($statement, &$linkData)
   {
      $statement->bind_result (
         $linkData ['DestinationID']);
   }
}


?>
