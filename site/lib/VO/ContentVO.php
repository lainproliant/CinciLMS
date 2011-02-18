<?php
include_once "DAO/ContentDAO.php";

class CourseContentVO {
   var $contentID, $parentID, $ownerID, $typeID, $name, $accessFlags;
   
   public static function byContentID ($contentID) {
      $dao = new CourseContentDAO ();
      return self::fromResult ($dao->byContentID ($contentID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->contentID = $result ["ContentID"];
      $obj->parentID = $result ["ParentID"];
      $obj->ownerID = $result ["OwnerID"];
      $obj->typeID = $result ["TypeID"];
      $obj->name = $result ["Name"];
      $obj->accessFlags = $result ["AccessFlags"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["ContentID"] = $this->contentID;
      $data ["ParentID"] = $this->parentID;
      $data ["OwnerID"] = $this->ownerID;
      $data ["TypeID"] = $this->typeID;
      $data ["Name"] = $this->name;
      $data ["AccessFlags"] = $this->accessFlags;
      return $data;
   }
   
   public function insert () {
      $dao = new CourseContentDAO ();
      $this->contentID = $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new CourseContentDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new CourseContentDAO ();
      $dao->delete ($this->contentID);
   }
}

class ContentItemsVO {
   var $itemID, $title, $text;
   
   public static function byItemID ($itemID) {
      $dao = new ContentItemsDAO ();
      return self::fromResult ($dao->byItemID ($itemID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->itemID = $result ["ItemID"];
      $obj->title = $result ["Title"];
      $obj->text = $result ["Text"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["ItemID"] = $this->itemID;
      $data ["Title"] = $this->title;
      $data ["Text"] = $this->text;
      return $data;
   }
   
   public function insert () {
      $dao = new ContentItemsDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new ContentItemsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new ContentItemsDAO ();
      $dao->delete ($this->itemID);
   }
}

class ContentLinksVO {
   var $linkID, $destinationID;
   
   public static function byLinkID ($linkID) {
      $dao = new ContentLinksDAO ();
      return self::fromResult ($dao->byLinkID ($linkID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->linkID = $result ["LinkID"];
      $obj->destinationID = $result ["DestinationID"];
      return $obj;
   }
   
   public static function listByDestinationID ($destinationID) {
      $dao = new ContentLinksDAO ();
      $objs = array ();
      foreach ($dao->listByDestinationID ($destinationID) as $result) {
         $objs [] = self::fromResult ($result);
      }
      return $objs;
   }
   
   protected function toData () {
      $data = array ();
      $data ["LinkID"] = $this->linkID;
      $data ["DestinationID"] = $this->destinationID;
      return $data;
   }
   
   public function insert () {
      $dao = new ContentLinksDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new ContentLinksDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new ContentLinksDAO ();
      $dao->delete ($this->linkID);
   }
}

class ContentItemAttachmentsVO {
   var $contentID, $fileID;
   
   public static function byContentID_FileID ($contentID, $fileID) {
      $dao = new ContentItemAttachmentsDAO ();
      return self::fromResult ($dao->byContentID_FileID ($contentID, $fileID));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->contentID = $result ["ContentID"];
      $obj->fileID = $result ["FileID"];
      return $obj;
   }
   
   public static function listByContentID ($contentID) {
      $dao = new ContentItemAttachmentsDAO ();
      $objs = array ();
      foreach ($dao->listByContentID ($contentID) as $result) {
         $objs [] = self::fromResult ($result);
      }
      return $objs;
   }
   
   protected function toData () {
      $data = array ();
      $data ["ContentID"] = $this->contentID;
      $data ["FileID"] = $this->fileID;
      return $data;
   }
   
   public function insert () {
      $dao = new ContentItemAttachmentsDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new ContentItemAttachmentsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new ContentItemAttachmentsDAO ();
      $dao->delete ($this->contentID, $this->fileID);
   }
}

class FactFolderContentsVO {
   var $folderID, $contentID, $path;
   
   public static function byFolderID_ContentID ($folderID, $contentID) {
      $dao = new FactFolderContentsDAO ();
      return self::fromResult ($dao->byFolderID_ContentID ($folderID, $contentID));
   }
   
   public static function byFolderID_Path ($folderID, $path) {
      $dao = new FactFolderContentsDAO ();
      return self::fromResult ($dao->byFolderID_Path ($folderID, $path));
   }
   
   protected static function fromResult ($result) {
      $obj = new static ();
      $obj->folderID = $result ["FolderID"];
      $obj->contentID = $result ["ContentID"];
      $obj->path = $result ["Path"];
      return $obj;
   }
   
   protected function toData () {
      $data = array ();
      $data ["FolderID"] = $this->folderID;
      $data ["ContentID"] = $this->contentID;
      $data ["Path"] = $this->path;
      return $data;
   }
   
   public function insert () {
      $dao = new FactFolderContentsDAO ();
      $dao->insert ($this->toData ());
   }
   public function save () {
      $dao = new FactFolderContentsDAO ();
      $dao->save ($this->toData ());
   }
   
   public function delete () {
      $dao = new FactFolderContentsDAO ();
      $dao->delete ($this->folderID, $this->contentID);
   }
}

?>
