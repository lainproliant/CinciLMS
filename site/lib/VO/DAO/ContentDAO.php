<?php
include_once "DAOException.php";

class CourseContentDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byContentID ($contentID) {
      $stmt = $this->db->prepare ("select ContentID, ParentID, OwnerID, TypeID, Name, AccessFlags, CreationTime from CourseContent where ContentID = ?");
      $stmt->bind_param ("i", $contentID);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["ContentID"], $results ["ParentID"], $results ["OwnerID"], $results ["TypeID"], $results ["Name"], $results ["AccessFlags"], $results ["CreationTime"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      
      $stmt->close ();
      return $results;
   }
   
   public function fetchAll () {
      $resultsList = array ();
      $results = array ();
      $query = "select ContentID, ParentID, OwnerID, TypeID, Name, AccessFlags, CreationTime from CourseContent";
      $stmt = $this->db->prepare ($query);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ContentID"], $results ["ParentID"], $results ["OwnerID"], $results ["TypeID"], $results ["Name"], $results ["AccessFlags"], $results ["CreationTime"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select ContentID, ParentID, OwnerID, TypeID, Name, AccessFlags, CreationTime from CourseContent where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ContentID"], $results ["ParentID"], $results ["OwnerID"], $results ["TypeID"], $results ["Name"], $results ["AccessFlags"], $results ["CreationTime"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into CourseContent (ParentID, OwnerID, TypeID, Name, AccessFlags, CreationTime) values (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param ("iiisss", $data ["ParentID"], $data ["OwnerID"], $data ["TypeID"], $data ["Name"], $data ["AccessFlags"], $data ["CreationTime"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"CourseContent\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update CourseContent set ParentID = ?, OwnerID = ?, TypeID = ?, Name = ?, AccessFlags = ?, CreationTime = ? where ContentID = ?");
      $stmt->bind_param ("iiisssi", $data ["ParentID"], $data ["OwnerID"], $data ["TypeID"], $data ["Name"], $data ["AccessFlags"], $data ["CreationTime"], $data ["ContentID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"CourseContent\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($contentID) {
      $stmt = $this->db->prepare ("delete from CourseContent where ContentID = ?");
      $stmt->bind_param ("i", $contentID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"CourseContent\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class ContentItemsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byItemID ($itemID) {
      $stmt = $this->db->prepare ("select ItemID, Title, Text from ContentItems where ItemID = ?");
      $stmt->bind_param ("i", $itemID);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["ItemID"], $results ["Title"], $results ["Text"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      
      $stmt->close ();
      return $results;
   }
   
   public function fetchAll () {
      $resultsList = array ();
      $results = array ();
      $query = "select ItemID, Title, Text from ContentItems";
      $stmt = $this->db->prepare ($query);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ItemID"], $results ["Title"], $results ["Text"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select ItemID, Title, Text from ContentItems where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ItemID"], $results ["Title"], $results ["Text"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into ContentItems (ItemID, Title, Text) values (?, ?, ?)");
      $stmt->bind_param ("iss", $data ["ItemID"], $data ["Title"], $data ["Text"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"ContentItems\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update ContentItems set Title = ?, Text = ? where ItemID = ?");
      $stmt->bind_param ("ssi", $data ["Title"], $data ["Text"], $data ["ItemID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"ContentItems\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($itemID) {
      $stmt = $this->db->prepare ("delete from ContentItems where ItemID = ?");
      $stmt->bind_param ("i", $itemID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"ContentItems\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class ContentLinksDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byLinkID ($linkID) {
      $stmt = $this->db->prepare ("select LinkID, DestinationID from ContentLinks where LinkID = ?");
      $stmt->bind_param ("i", $linkID);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["LinkID"], $results ["DestinationID"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      
      $stmt->close ();
      return $results;
   }
   
   public function listByDestinationID ($destinationID) {
      $resultsList = array ();
      $results = array ();
      $stmt = $this->db->prepare ("select LinkID, DestinationID from ContentLinks where DestinationID = ?");
      $stmt->bind_param ("i", $destinationID);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["LinkID"], $results ["DestinationID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function fetchAll () {
      $resultsList = array ();
      $results = array ();
      $query = "select LinkID, DestinationID from ContentLinks";
      $stmt = $this->db->prepare ($query);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["LinkID"], $results ["DestinationID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select LinkID, DestinationID from ContentLinks where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["LinkID"], $results ["DestinationID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into ContentLinks (LinkID, DestinationID) values (?, ?)");
      $stmt->bind_param ("ii", $data ["LinkID"], $data ["DestinationID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"ContentLinks\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update ContentLinks set DestinationID = ? where LinkID = ?");
      $stmt->bind_param ("ii", $data ["DestinationID"], $data ["LinkID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"ContentLinks\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($linkID) {
      $stmt = $this->db->prepare ("delete from ContentLinks where LinkID = ?");
      $stmt->bind_param ("i", $linkID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"ContentLinks\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class ContentItemAttachmentsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byContentID_FileID ($contentID, $fileID) {
      $stmt = $this->db->prepare ("select ContentID, FileID from ContentItemAttachments where ContentID = ? and FileID = ?");
      $stmt->bind_param ("ii", $contentID, $fileID);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["ContentID"], $results ["FileID"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      
      $stmt->close ();
      return $results;
   }
   
   public function listByContentID ($contentID) {
      $resultsList = array ();
      $results = array ();
      $stmt = $this->db->prepare ("select ContentID, FileID from ContentItemAttachments where ContentID = ?");
      $stmt->bind_param ("i", $contentID);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ContentID"], $results ["FileID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function fetchAll () {
      $resultsList = array ();
      $results = array ();
      $query = "select ContentID, FileID from ContentItemAttachments";
      $stmt = $this->db->prepare ($query);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ContentID"], $results ["FileID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select ContentID, FileID from ContentItemAttachments where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["ContentID"], $results ["FileID"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into ContentItemAttachments (ContentID, FileID) values (?, ?)");
      $stmt->bind_param ("ii", $data ["ContentID"], $data ["FileID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"ContentItemAttachments\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update ContentItemAttachments set  where ContentID = ? and FileID = ?");
      $stmt->bind_param ("ii", $data ["ContentID"], $data ["FileID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"ContentItemAttachments\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($contentID, $fileID) {
      $stmt = $this->db->prepare ("delete from ContentItemAttachments where ContentID = ? and FileID = ?");
      $stmt->bind_param ("ii", $contentID, $fileID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"ContentItemAttachments\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

class FactFolderContentsDAO {
   function __construct () {
      $this->db = databaseConnect ();
   }
   
   public function byFolderID_ContentID ($folderID, $contentID) {
      $stmt = $this->db->prepare ("select FolderID, ContentID, Path from FactFolderContents where FolderID = ? and ContentID = ?");
      $stmt->bind_param ("ii", $folderID, $contentID);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["FolderID"], $results ["ContentID"], $results ["Path"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      
      $stmt->close ();
      return $results;
   }
   
   public function byFolderID_Path ($folderID, $path) {
      $stmt = $this->db->prepare ("select FolderID, ContentID, Path from FactFolderContents where FolderID = ? and Path = ?");
      $stmt->bind_param ("is", $folderID, $path);
      $stmt->execute ();
      $stmt->store_result ();
      $stmt->bind_result ($results ["FolderID"], $results ["ContentID"], $results ["Path"]);
      $stmt->fetch ();
      if ($stmt->num_rows < 1) {
         $results = NULL;
      }
      $stmt->close ();
      return $results;
   }
   
   public function fetchAll () {
      $resultsList = array ();
      $results = array ();
      $query = "select FolderID, ContentID, Path from FactFolderContents";
      $stmt = $this->db->prepare ($query);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["FolderID"], $results ["ContentID"], $results ["Path"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function search ($params, $postfix = "") {
      $resultsList = array ();
      $results = array ();
      $bindTypes = "";
      foreach (array_values ($params) as $val) {
         if (is_int ($val)) { $bindTypes .= 'i'; }
         elseif (is_string ($val)) { $bindTypes .= 's'; }
         elseif (is_float ($val)) { $bindTypes .= 'd'; }
      }
      $query = sprintf ("select FolderID, ContentID, Path from FactFolderContents where %s", implode (" and ", array_keys ($params)));
      $query .= ' ' . $postfix;
      $bindParamArgs = array (&$bindTypes);
      foreach ($params as $key => $value) {
         $bindParamArgs [$key] = &$params [$key];
      }
      $stmt = $this->db->prepare ($query);
      call_user_func_array (array ($stmt, "bind_param"), $bindParamArgs);
      $stmt->execute ();
      $stmt->store_result ();
      $lambda = create_function ('$a', 'return $a;');
      $stmt->bind_result ($results ["FolderID"], $results ["ContentID"], $results ["Path"]);
      while ($stmt->fetch ()) {
         $resultsList [] = array_map ($lambda, $results);
      }
      $stmt->close ();
      return $resultsList;
   }
   
   public function insert ($data) {
      $stmt = $this->db->prepare ("insert into FactFolderContents (FolderID, ContentID, Path) values (?, ?, ?)");
      $stmt->bind_param ("iis", $data ["FolderID"], $data ["ContentID"], $data ["Path"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't insert record in the table \"FactFolderContents\"", $stmt->error, $stmt->affected_rows);
      }
      return $this->db->insert_id;
   }
   
   public function save ($data) {
      $stmt = $this->db->prepare ("update FactFolderContents set Path = ? where FolderID = ? and ContentID = ?");
      $stmt->bind_param ("sii", $data ["Path"], $data ["FolderID"], $data ["ContentID"]);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Couldn't save record in table \"FactFolderContents\"", $stmt->error, $stmt->affected_rows);
      }
   }
   
   public function delete ($folderID, $contentID) {
      $stmt = $this->db->prepare ("delete from FactFolderContents where FolderID = ? and ContentID = ?");
      $stmt->bind_param ("ii", $folderID, $contentID);
      $stmt->execute ();
      if ($stmt->affected_rows != 1) {
         throw new DAOException ("Problem deleting record from table \"FactFolderContents\"", $stmt->error, $stmt->affected_rows);
      }
   }
}

?>
