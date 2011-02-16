<?php

/*
 * Content: Classes representing basic course content. 
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "VO/ContentVO.php";

define ('CONTENT_TYPE_ITEM', 1);
define ('CONTENT_TYPE_ASSIGNMENT', 2);
define ('CONTENT_TYPE_FOLDER', 3);
define ('CONTENT_TYPE_LINK', 4);

class ContentItem extends ContentItemVO {
   public function __construct ($courseContent = NULL, $parentID = NULL, $ownerID = NULL, $name = NULL)
   {
      parent::__construct ();
      
      if (empty ($courseContent)) {
         $courseContent = new CourseContentVO ();
         $courseContent->contentID = NULL;
         $courseContent->parentID = $parentID;
         $courseContent->ownerID = $ownerID;
         $courseContent->typeID = CONTENT_TYPE_ITEM;
         $courseContent->name = $name;
         
         // LRS-TODO: Move to insert method.
         $contentTypeInfo = ContentTypesVO::byTypeID (CONTENT_TYPE_ITEM);
         $courseContent->accessFlags = $contentTypeInfo->defaultAccess;
      }

      $this->courseContent = $courseContent;
   }

   public function insert ()
   {
      // Insert the CourseContent object first!
      $contentTypeInfo = ContentTypesVO::byTypeID (CONTENT_TYPE_ITEM);
      $courseContent->accessFlags = $contentTypeInfo->defaultAccess;

      // Fetch the new content ID for this object.
      $courseContent->insert ();
      $this->itemID = $courseContent->contentID;

      // Do the insert for this type of object.
      parent::insert ();
   }
}

