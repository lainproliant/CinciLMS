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

class ContentException extends CinciException {
   function __construct ($contentID)
   {
      parent::construct ("Content Error!",
         sprintf ("There was an error interpreting course content with ID %d.", $contentID));
   }
}


class CourseContent extends CourseContentVO {
   /*
    * Fetches the title and text of the ContentItem.
    * Throws a ContentException if the content object
    * does not refer to a content item, or DAOException
    * if there was an error fetching the info from the
    * database.
    */
   public function getContentItemInfo ()
   {
      if ($this->contentID != CONTENT_TYPE_ITEM) {
         throw new ContentException ($this->contentID);
      }

      $item = ContentItemsVO::byItemID ($this->contentID);
      return $item;
   }

   /*
    * Fetches a list of child item IDs for this item
    * as a folder.
    * Throws a ContentException if the content object
    * is not a folder, or DAOException if ther was
    * an error fetching the info from the database.
    */
   public function getFolderContents ()
   {
      if ($this->contentID != CONTENT_ITEM_FOLDER) {
         throw new ContentException ($this->contentID);
      }

      $folderContents = array ();
      $entries = FactFolderContentsVO::listByFolderID ($this->contentID);

      foreach ($entries as $entry) {
         $folderContents [] = $entry->contentID;
      }

      return $folderContents;
   }
}
      

