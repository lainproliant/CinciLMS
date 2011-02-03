<?php

/*
 * Course: Class representations of courses and related items in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

class CourseContent {
   function __construct ()
   {
      $this->ContentID = 0;
      $this->ParentID = 0;
      $this->OwnerID = 0;
      $this->TypeID = 0;
      $this->Name = "";
      $this->AccessFlags = [];
   }

   /*
    * Retrieves a generic Content item from the DAO via ContentID.
    * 
    * ContentID:     The ContentID of the generic content item.
    *
    * Raises CourseContentException if the content was not found.
    */
   public static function byContentID ($ContentID)
   {
      $DAO = new $CourseContentDAO;

      $content = new CourseContent ($DAO->byContentID ($ContentID));
   }

   /*
    * PRIVATE
    * Promotes the given class to its appropriate subclass.
    */
   private function promote ()
   {
      switch ($content->TypeID) {
      case CONTENT_TYPE_ITEM:
         $DAO = new ContentItemDAO ();
         return new ContentItem ($DAO->byLinkID ($ContentID));

      case CONTENT_TYPE_ASSIGNMENT:
         $DAO = new AssignmentItemDAO ();
         return new AssignmentItem ($DAO->byAssignmentID ($ContentID));

      case CONTENT_TYPE_FOLDER:
         $DAO = new ContentFolderDAO ();
         return new ContentFolder ($DAO->byFolderID ($ContentID));

      case CONTENT_TYPE_LINK:
         $DAO = new ContentLinkDAO ();
         return new ContentLink ($DAO->byItemID ($ContentID));

      default:
         return $this;
      }
   }

   /*
    * PRIVATE
    * Constructs a CourseContent object from contentData returned from the DAO.
    */
   protected function fromData ($contentData)
   {
      $this->ContentID     = $contentData ['ContentID'];
      $this->ParentID      = $contentData ['ParentID'];
      $this->OwnerID       = $contentData ['OwnerID'];
      $this->TypeID        = $contentData ['TypeID'];
      $this->Name          = $contentData ['Name'];
      $this->AccessFlags   = $contentData ['AccessFlags'];
   }

   /*
    * PRIVATE
    * Creates courseData for the DAO object.
    */
   protected function toData ()
   {
      $contentData = array ();

      $contentData ['ContentID'] = $this->ContentID;
      $contentData ['ParentID'] = $this->ParentID;
      $contentData ['OwnerID'] = $this->OwnerID;
      $contentData ['TypeID'] = $this->TypeID;
      $contentData ['Name'] = $this->Name;
      $contentData ['AccessFlags'] = $this->AccessFlags;
   }
}

class ContentLink extends CourseContent {
   function __construct ()
   {
      parent::__construct ($contentData = NULL);

      $self->DestinationID = 0;

      if (! empty ($contentData)) {
         parent::fromData ($contentData);
      }

   }

   public static function byLinkID ($LinkID)
   {
      $DAO = new ContentLinkDAO (); 

      $linkData = $DAO->byLinkID ($LinkID);
      return fromData ($linkData);
   }

   protected function fromData ($linkData)
   {
      $this->ContentID        = $linkData ['ContentID'];
      $this->DestinationID    = $linkData ['DestinationID'];
   }
}


?>
