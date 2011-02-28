<?php

/*
 * FolderJoinDAO: Left-joining methods against the FactFolderContentsVO table.
 */
class FolderJoinDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }

   /*
    * Joins the given folder's contents against the CourseContent table.
    */
   public function joinFolderID_FolderContents ($folderID)
   {
      $resultArrays = array ();
      $resultArrays [0] = array ();
      $resultArrays [1] = array ();

      $folderResults = array ();
      $contentResults = array ();

      $query = 
         <<<QRY
         select
            FactFolderContents.FolderID,
            FactFolderContents.ContentID,
            FactFolderContents.Path,
            CourseContent.ContentID,
            CourseContent.ParentID,
            CourseContent.OwnerID,
            CourseContent.TypeID,
            CourseContent.Name,
            CourseContent.AccessFlags,
            CourseContent.CreationTime

         from FactFolderContents, CourseContent
         
         where
            FactFolderContents.ContentID = CourseContent.ContentID
         and
            FactFolderContents.FolderID = ?;
QRY;

      $stmt = $this->db->prepare ($query);
      $stmt->bind_param ("i", $folderID);
      $stmt->execute ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result (
         $folderResults ["FolderID"],
         $folderResults ["ContentID"],
         $folderResults ["Path"], 
         $contentResults ["ContentID"],
         $contentResults ["ParentID"],
         $contentResults ["OwnerID"],
         $contentResults ["TypeID"],
         $contentResults ["Name"],
         $contentResults ["AccessFlags"],
         $contentResults ["CreationTime"]);

      while ($stmt->fetch ()) {
         $resultArrays [0][] = array_map ($lambda, $folderResults);
         $resultArrays [1][] = array_map ($lambda, $contentResults);
      }

      $stmt->close ();
      return $resultArrays;
   }
}

?>
