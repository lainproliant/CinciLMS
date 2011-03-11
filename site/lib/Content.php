<?php

/*
 * Content: Classes representing basic course content. 
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "Exceptions.php";
include_once "util/nbbc.php";

include_once "VO/ContentVO.php";
include_once "FolderJoin.php";

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

      case CONTENT_TYPE_ASSIGNMENT:
         throw new CinciException ("Not Implemented",
            "Assignment items are not yet implemented.");

      case CONTENT_TYPE_FOLDER:
         return new ContentFolder ($this);

      case CONTENT_TYPE_LINK:
         return new ContentLink ($this);
      }
   }

   /*
    * Checks read permissions on the item based on system permissions
    * course permissions, user permissions, and item permissions.
    *
    * authority:        The user's current AuthorityClass instance.
    * user:             The User instance.  Can be NULL, in which case
    *                   the user is treated as a guest.
    * course:           The course in which the item is contained.
    * enrollment:       The user's enrollment in the course.
    *                   Can be NULL, in which case the user is treated
    *                   as a guest.
    *
    * Returns True if the item can be read, False otherwise.
    */
   public function checkReadAccess ($authority, $user, $course, $enrollment)
   {
      // If the authority contains '_sysopReadWrite' permissions, grant access.
      if ($authority->authorizeCheck ('_sysopReadWrite')) {
         return TRUE;
      }
      
      // Explode (split into an array) each of the access flag sets,
      // or provide defaults if a course enrollment was not specified.
      $itemPermissions =   explode (',', $this->accessFlags);
      $coursePermissions = explode (',', $course->accessFlags);
      $userPermissions =   empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);
   
      // Determine the user's role in the course.  If there is no course enrollment,
      // the user is assumed to be a guest.
      $courseRole = empty ($enrollment) ?
         COURSE_ROLE_GUEST :
         $enrollment->roleID;
      
      // Determine the user ID if there is one.
      $userID = empty ($user) ?
         NULL :
         $user->userID;
      
      // Confirm that course read permissions are enabled for the user.
      // LRS-TODO: Implement guest read access for content.
      if (in_array ('CR', $userPermissions)) {
         // What is the user's course role?
         switch ($courseRole) {
         case COURSE_ROLE_INSTRUCTOR:
            // Instructors always have full read permission to their courses
            // and items therein, unless their user read permissions are
            // disabled.
            return TRUE;

         case COURSE_ROLE_STUDENT:
            // Check the member read permissions for the course.
            if (in_array ('MR', $coursePermissions)) {
               // Does the user own this content?
               if ($this->ownerID == $userID) {
                  // Are owner read permissions enabled for the item?
                  if (in_array ('UR', $itemPermissions)) {
                     // Access granted.
                     return TRUE;
                  }
               }

               // The user does not own the content or owner read permissions are disabled.
               // Are member read permissions enabled for the item?
               if (in_array ('UR', $itemPermissions)) {
                  // Access granted.
                  return TRUE;
               } else {
                  // Access denied.
                  return FALSE;
               }

            } else {
               // Access denied, because the course is locked from members.
               return FALSE;
            }

         case COURSE_ROLE_BUILDER:
         case COURSE_ROLE_ASSISTANT:
            // Check the user read permissions for the course and the item.
            if (in_array ('UR', $coursePermissions) and in_array ('UR', $itemPermissions)) {
               // Access granted to the builder/TA user.
               return TRUE;
            } else {
               // Access denied, because the course is locked from builders/TAs
               // or the item is locked from builders/TAs.
               return FALSE;
            }

         default:
            // Encountered an unmanaged user role type.  Access denied.
            return FALSE;
         }
      } else {
         // Course read permissions are disabled for the user.  Access denied.
         return FALSE;
      }

   }

   /*
    * Checks write permissions on the item based on system permissions
    * course permissions, user permissions, and item permissions.
    *
    * authority:        The user's current AuthorityClass instance.
    * user:             The User instance.  Can be NULL, in which case
    *                   the user is treated as a guest.
    * course:           The course in which the item is contained.
    * enrollment:       The user's enrollment in the course.
    *                   Can be NULL, in which case the user is treated
    *                   as a guest.
    *
    * Returns True if the item can be written, False otherwise.
    */
   public function checkWriteAccess ($authority, $user, $course, $enrollment)
   {
      // If the authority contains '_sysopReadWrite' permissions, grant access.
      // Otherwise, if no enrollment was provided, deny access.
      if ($authority->authorizeCheck ('_sysopReadWrite')) {
         return TRUE;
      }

      // Explode (split into an array) each of the access flag sets,
      // or provide defaults if a course enrollment was not specified.
      $itemPermissions =   explode (',', $this->accessFlags);
      $coursePermissions = explode (',', $course->accessFlags);
      $userPermissions =   empty ($enrollment) ?
         array () :
         explode (',', $enrollment->accessFlags);

      // Determine the user's role in the course.  If there is no course enrollment,
      // the user is assumed to be a guest.
      $courseRole = empty ($enrollment) ?
         COURSE_ROLE_GUEST :
         $enrollment->roleID;

      // Determine the user ID if there is one.
      $userID = empty ($user) ?
         NULL :
         $user->userID;

      // Confirm that course write permissions are enabled for the user.
      if (in_array ('CW', $userPermissions)) {
         // What is the user's course role?
         switch ($courseRole) {
         case COURSE_ROLE_INSTRUCTOR:
            // Instructors always have full write permission to their courses
            // and items therein, unless their user read permissions are
            // disabled.
            return TRUE;

         case COURSE_ROLE_STUDENT:
         case COURSE_ROLE_ASSISTANT:
            // Check the member write permissions for the course.
            if (in_array ('MW', $coursePermissions)) {
               // Does the user own this content?
               if ($this->ownerID == $userID) {
                  // Are owner write permissions enabled for the item?
                  if (in_array ('UW', $itemPermissions)) {
                     // Access granted.
                     return TRUE;
                  }
               }

               // The user does not own the content or owner write permissions are disabled.
               // Are member write permissions enabled for the item?
               if (in_array ('UW', $itemPermissions)) {
                  // Access granted.
                  return TRUE;
               } else {
                  // Access denied.
                  return FALSE;
               }

            } else {
               // Access denied, because the course is locked from members.
               return FALSE;
            }

         case COURSE_ROLE_BUILDER:
            // Check the user write permissions for the course and the item.
            if (in_array ('UW', $coursePermissions) and in_array ('UW', $itemPermissions)) {
               // Access granted to the builder/TA user.
               return TRUE;
            } else {
               // Access denied, because the course is locked from builders/TAs
               // or the item is locked from builders/TAs.
               return FALSE;
            }

         default:
            // Encountered an unmanaged user role type.  Access denied.
            return FALSE;
         }
      } else {
         // Course write permissions are disabled for the user.  Access denied.
         return FALSE;
      }
   }

   /*
    * Adds appropriate context menus to the given AuthorityClass instance.
    * 
    * authority:        The user's current AuthorityClass instance.
    * user:             The User instance.  Can be NULL, in which case
    *                   the user is treated as a guest.
    * course:           The course in which the item is contained.
    * enrollment:       The user's enrollment in the course.
    *                   Can be NULL, in which case the user is treated
    *                   as a guest.
    */
   public function addContext ($authority, $user, $course, $enrollment)
   {
      if ($this->checkWriteAccess ($authority, $user, $course, $enrollment)) {
         $createMenu = $authority->getMenu ()->appendSubmenu ('Create');

         // Add course content creation items. 
         $creationParent = sprintf ("?parent=%s", htmlentities ($this->pathName));
         $newFolderAction = $creationParent . '&action=newContent&contentType=folder';
         $newItemAction = $creationParent . '&action=newContent&contentType=item';

         $createMenu->addItems (array (
            'New Folder' =>      new HyperlinkAction ($newFolderAction),
            'New Content Item' => new HyperlinkAction ($newItemAction)));
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

      /*
       * A pseudo-column representing the path of the item.
       * This column is populated when content items
       * are retrieved via ContentFolder::getFolderContents ().
       */
      $this->pathName = NULL;
   }

   /* 
    * Overridden method to insert a new CourseContent object
    * and its appropriate subtype data.
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
   public function save ($saveVO = TRUE)
   {
      parent::save ();

      $vo = $this->createVO ();

      if (! empty ($vo) and $saveVO) {
         $vo->save ();
      }
   }
   
   /* ABSTRACT
    * Displays the course content in the provided Div.
    * Subclasses must implement this method.
    */
   public abstract function display ($contentDiv, $path, $authority, $user,
      $course, $enrollment);

   /* ABSTRACT
    * Displays a iconified representation of the content in
    * the provided Div.  Subclasses must implement this method.
    */
   public abstract function displayItem ($contentDiv, $path, $authority, $user,
      $course, $enrollment);

   /* ABSTRACT
    * Create an appropriate value object so that subtype
    * information can be saved.  Must be implemented.
    * Subclass implementations may return NULL to inform
    * that there is not an appropriate value object type.
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

   /* LRS-TODO: Implement
    * Displays the course content in the provided Div.
    * Subclasses must implement this method.
    */
   public function display ($contentDiv, $path, $authority, $user,
      $course, $enrollment) { }
   
   /*
    * Displays a iconified representation of the content in
    * the provided Div.  Subclasses must implement this method.
    */
   public function displayItem ($contentDiv, $path, $authority, $user,
      $course, $enrollment)
   {
      $itemInfo = $this->getContentItemInfo ();

      $p = new XMLEntity ($contentDiv, "p");
      $p->setAttribute ('class', 'content-item item');
      
      new Span ($p, htmlentities ($this->name), 'title');
      new Br ($p);
      
      new Span ($p, $itemInfo->text);
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
    * Adds the given CourseContent object to this folder.
    *
    * content:       The content to add to the folder.
    * path:          A path to the content.  Derived from the content name
    *                if not provided.
    * 
    * Returns a new FactFolderContentsVO object representing the
    * content within the folder.  Throws a DAOException if the
    * item could not be added to the folder, possibly because
    * an item exists exists at the given path.
    */
   public function addContent ($content, $path = NULL)
   {
      if (empty ($path)) {
         $path = anumfilter ($content->name);
      }

      $folderEntry = new FactFolderContentsVO ();
      $folderEntry->folderID = $this->contentID;
      $folderEntry->contentID = $content->contentID;
      $folderEntry->path = $path;

      $folderEntry->insert ();

      $content->parentID = $this->contentID;
      $content->save (FALSE);
   }

   /*
    * Fetches a list of resolved CourseContent items contained within this
    * folder.  The CourseContent items will have a non-NULL pathName,
    * which can be used to determine the relative path of the items.
    */
   public function getFolderContents ()
   {
      $courseContents = array ();
      $folderContents = FolderJoin::joinFolderID_FolderContents ($this->contentID);

      for ($x = 0; $x < count ($folderContents [0]); $x++) {
         $folderContents [1][$x]->pathName = $folderContents [0][$x]->path;
         $courseContents [] = $folderContents [1][$x];
      }

      return $courseContents;
   }

   /*
    * Resolves the given path relative to this folder.
    *
    * pathArray:        The path to resolve as an array.
    * authority:        The user's current AuthorityClass instance.
    * user:             The user for which permissions will be determined.
    * course:           The course containing the content.
    * enrollment: The enrollment record for the user in this course.
    *
    * Returns a CourseContentSubtype for the content retrieved.
    * Throws a CinciAccessException if the path doesn't exist or the
    * given user doesn't have access.
    */

   // TODO: Check for permissions upon content access, folder, or
   //       link dereference.
   public function resolvePath ($pathArray, $authority, $user, $course,
      $enrollment = NULL, $cdCheckSet = NULL) 
   {      
      // A set for circular dependency checking.
      if (empty ($cdCheckSet)) {
         $cdCheckSet = array ();
      }

      if (array_key_exists ($this->contentID, $cdCheckSet)) {
         // A circular dependency was detected. throw an exception.
         throw new CinciException ("Circular Dependency Error",
            "The given path contains a circular path dependency.  Please contact a system administrator to resolve this problem.");

      } else {
         // Add the current directory to the circular dependency check set.
         $cdCheckSet [$this->contentID] = 1;
      }

      $path = array_shift ($pathArray);
      
      $folderContents = FactFolderContentsVO::byFolderID_Path (
         $this->contentID, $path);

      if (empty ($folderContents->contentID)) {
         // The specified path doesn't exist.
         throw new CinciAccessException ("The requested content does not exist.");
      }

      $content = CourseContent::byContentID (
         $folderContents->contentID)->resolve ();
      
      if (! $content->checkReadAccess ($authority, $user, $course, $enrollment)) {
         // Access to the resource was denied.  Throw an exception.
         throw new CinciAccessException (
            "You are not authorized to access the requested content.");   
      }
      
      if ($content->typeID == CONTENT_TYPE_LINK) {
         $content = $content->dereference (
            $authority, $user, $course, $enrollment, $cdCheckSet);
      }

      if (! empty ($pathArray)) {
         if ($content->typeID != CONTENT_TYPE_FOLDER) {
            // The path says we need to recurse further, but the current
            // node in the directory tree is not a folder!
            throw new CinciAccessException (sprintf (
               "The content named \"%s\" is not a folder.", $content->name));
         }

         // Recurse into the next directory.
         return $content->resolvePath ($pathArray, $authority, $user,
            $course, $enrollment, $cdCheckSet);

      } else {
         return $content;
      }
   }

   /*
    * Displays the contents of this folder.
    *
    * contentDiv:       The content div into which the item is to be displayed.
    * path:             The absolute path of the content.
    * authority:        The user's authority class.
    * user:             The user accessing the content.
    * course:           The course in context of which the item is viewed.
    * enrollment:       The user's enrollment in the course.  May be NULL.
    */
   public function display ($contentDiv, $path, $authority, $user,
      $course, $enrollment)
   {
      $folderContents = $this->getFolderContents ();

      foreach ($folderContents as $content) {
         $subpath = $path . '/' . $content->pathName;
         $content->displayItem ($contentDiv, $subpath, $authority, $user,
            $course, $enrollment);
      }
   }

   /*
    * Adds a representation of this object to the given Div
    * a decorated Div.
    *
    * contentDiv:       The content div into which the item is to be displayed.
    * path:             The absolute path of the content.
    * authority:        The user's authority class.
    * user:             The user accessing the content.
    * course:           The course in context of which the item is viewed.
    * enrollment:       The user's enrollment in the course.  May be NULL.
    */
   public function displayItem ($contentDiv, $path, $authority, $user,
      $course, $enrollment)
   {
      $link = new Hyperlink ($contentDiv, sprintf (
         "?action=view&path=%s", htmlentities ($path)));
      $link->setAttribute ('class', 'content-item folder');

      new Span ($link, htmlentities ($this->name), 'title');
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
    * Dereferences the link and returns the course content
    * to which it points.
    *
    * authority:        The current AuthorityClass instance for the user.
    * user:             The user for which permissions will be determined.
    * course:           The course in context of which this content is accessed.
    * courseEnrolment:  The enrollment record for the user in this course.
    * cdCheckSet:       A Set of ContentIDs used to prevent circular dependencies.
    *                   Pass in a Set if you are parsing a directory hierarchy
    *                   and dereferencing links.
    */
   public function dereference ($authority, $user, $course,
      $enrollment = NULL, $cdCheckSet = NULL)
   {
      // A set for circular dependency checking.
      if (empty ($cdCheckSet)) {
         $cdCheckSet = array ();
      }

      if (array_key_exists ($this->contentID, $cdCheckSet)) {
         // A circular dependency was detected in the link, throw an exception.
         throw new CinciException ("Circular Dependency Error",
            "The given path contains a link which causes a circular dependency.  Please contact a system administrator to resolve this problem.");
      } else {
         // Add this link to the circular dependency check set.
         $cdCheckSet [$this->contentID] = 1;
      }

      $link = ContentLinksVO::byLinkID ($this->contentID);

      $content = CourseContent::byContentID (
         $link->destinationID)->resolve ();

      if (empty ($content->contentID)) {
         // The content to which this link points does not exist.
         throw new CinciException ("Link Error",
            "The course content link is broken and cannot be dereferenced.");
      }

      if (! $content->checkReadAccess ($authority, $user, $course, $enrollment)) {
         // Access to the destination content was denied.
         throw new CinciAccessException ("You are not authorized to access the content linked to by this item.");
      }

      return $content;
   }

   /* LRS-TODO: Implement
    * Displays the course content in the provided Div.
    * Subclasses must implement this method.
    */
   public function display ($contentDiv, $path, $authority, $user,
      $course, $enrollment) { }

   /* LRS-TODO: Implement
    * Displays a iconified representation of the content in
    * the provided Div.  Subclasses must implement this method.
    */
   public function displayItem ($contentDiv, $path, $authority, $user,
      $course, $enrollment) { }

   protected function createVO ()
   {
      $vo = new ContentLinksVO ();
      $vo->linkID = $this->contentID;
      $vo->destinationID = $this->destinationID;

      return $vo;
   }
}

?>
