<?php

/*
 * Content: Classes representing basic course content. 
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "Exceptions.php";

include_once "VO/ContentVO.php";

define ('CONTENT_TYPE_ITEM', 1);
define ('CONTENT_TYPE_ASSIGNMENT', 2);
define ('CONTENT_TYPE_FOLDER', 3);
define ('CONTENT_TYPE_LINK', 4);

define ('CONTENT_DEFAULT_ACCESS', 'UR,UW,MR');

class ContentException extends CinciException {
   function __construct ($contentID)
   {
      parent::construct ("Content Error!",
         sprintf ("There was an error interpreting course content with ID %d.", $contentID));
   }
}

class CourseContent extends CourseContentVO {
   /*
    * Resolves the content item to an instance of the
    * appropriate CourseContentSubtype class.
    */
   public function resolve ()
   {
      switch ($this->typeID) {
      case CONTENT_TYPE_ITEM:
         return new ContentItem ($this);

      case CONTENT_ITEM_ASSIGNMENT:
         throw new CinciException ("Not Implemented",
            "Assignment items are not yet implemented.");

      case CONTENT_ITEM_FOLDER:
         return new ContentFolder ($this);

      case CONTENT_ITEM_LINK:
         return new ContentLink ($this);
      }
   }
}

abstract class CourseContentSubtype extends CourseContent {
   /*
    * Demotes the given CourseContent object to its
    * appropriate subtype.
    */
   function __construct ($contentItem = NULL) {
      if (! empty ($contentItem)) {
         $this->contentID = $contentItem->contentID;
         $this->parentID = $contentItem->parentID;
         $this->ownerID = $contentItem->ownerID;
         $this->typeID = $contentItem->typeID;
         $this->name = $contentItem->name;
         $this->accessFlags = $contentItem->accessFlags;
      } else {
         $this->contentID = NULL;
         $this->parentID = NULL;
         $this->ownerID = NULL;
         $this->typeID = NULL;
         $this->name = NULL;
         $this->accessFlags = CONTENT_DEFAULT_ACCESS;
      }
   }
   
   /* 
    * Overridden method to insert a new CourseContent object
    * and its appropraite subtype data.
    */
   public function insert ()
   {
      parent::insert ();

      $vo = $this->createVO ();
      
      if (! empty ($vo)) {
         $vo->insert ();
      }
   }

   /*
    * Overridden method to save a CourseContent object
    * and its appropriate subtype data.
    */
   public function save ()
   {
      parent::save ();

      $vo = $this->createVO ();

      if (! empty ($vo)) {
         $vo->save ();
      }
   }

   /* ABSTRACT
    * Create an appropriate value object so that subtype
    * information can be saved.  Must be implemented.
    * Subclass implementations may return NULL to inform
    * that there is not an appropriate.
    */
   protected abstract function createVO ();
}

class ContentItem extends CourseContentSubtype {
   function __construct ($contentItem = NULL) {
      parent::__construct ($contentItem);

      if (empty ($this->typeID)) {
         $this->typeID = CONTENT_TYPE_ITEM;
      }

      $this->title = NULL;
      $this->text = NULL;
   }

   /*
    * Fetches the title and text of the content item.
    */
   public function getContentItemInfo ()
   {
      $item = ContentItemsVO::byItemID ($this->contentID);
      return $item;
   }

   protected function createVO ()
   {
      $vo = new ContentItemsVO ();
      $vo->itemID = $this->contentID;
      $vo->title = $this->title;
      $vo->text = $this->text;

      return $vo;
   }
}

class ContentFolder extends CourseContentSubtype {
   function __construct ($contentItem = NULL) {
      parent::__construct ($contentItem);

      if (empty ($this->typeID)) {
         $this->typeID = CONTENT_TYPE_FOLDER;
      }
   }

   /*
    * Fetches a list of item IDs of the items contained
    * within the folder.
    */
   public function getFolderContents ()
   {
      $folderContents = array ();
      $entries = FactFolderContentsVO::listByFolderID ($this->contentID);

      foreach ($entries as $entry) {
         $folderContents [] = $entry->contentID;
      }

      return $folderContents;
   }
   
   /*
    * Folders in and of themselves do not have a unique value
    * object.  There is a fact table, FactFolderContents, which
    * represents folder contents, but CourseContentSubtype needs
    * not be concerned with these details.
    */
   protected function createVO ()
   { 
      return NULL;
   }

}

class ContentLink extends CourseContentSubtype {
   function __construct ($contentItem = NULL) {
      parent::__construct ($contentItem);

      if (empty ($this->typeID)) {
         $this->typeID = CONTENT_TYPE_LINK;
      }

      $this->destinationID = NULL;
   }

   /*
    * Fetches the destination ID of the given course link.
    */
   public function getDestination ()
   {
      $link = ContentLinksVO::byLinkID ($this->contentID);
      return $link->destinationID;
   }

   protected function createVO ()
   {
      $vo = new ContentLinksVO ();
      $vo->linkID = $this->contentID;
      $vo->destinationID = $this->destinationID;

      return $vo;
   }
}

?>
