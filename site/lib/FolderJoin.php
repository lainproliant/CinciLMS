<?php

include_once "Content.php";
include_once "VO/DAO/FolderJoinDAO.php";

/*
 * FolderJoin: The results of left-joining methods 
 *             against the FactFolderContents table.
 */
class FolderJoin {
   /*
    * Joins the given folder's contents against the CourseContent table.
    */
   public static function joinFolderID_FolderContents ($folderID)
   {
      $folderContents = array ();
      $courseContents = array ();
      
      $dao = new FolderJoinDAO ();
      $resultArrays = $dao->joinFolderID_FolderContents ($folderID);

      foreach ($resultArrays [0] as $folderData) {
         $folderContents [] = FactFolderContentsVO::fromResult (
            $folderData);
      }

      foreach ($resultArrays [1] as $contentData) {
         $courseContents [] = CourseContent::fromResult ($contentData)->resolve ();
      }

      return array ($folderContents, $courseContents);
   }
}

?>
