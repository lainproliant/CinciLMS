<?php

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";
include_once "Course.php";
include_once "Content.php";

/*
 * ContentForm: Forms for course content creation and editing. 
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

class FolderForm extends Form {
   function __construct ($parent, $action, $userClass, $folder = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'folder_form');

      # Include the content validation script.
      new Script ($this, 'lib/content.js');

      $this->setAttribute ('onSubmit', 'return folderFormValidate (this);');
      
      $folderName = NULL;
      $folderPath = NULL;
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;

      if (! empty ($course)) {
         $folderName = $folder->name;
         $folderPath = $folder->pathName;
         $accessFlags = $folder->accessFlags;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Folder Name:', 'folderName', 'first');
      new TextInput ($div, 'folderName', 'folderName', $courseName);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Folder Path:', 'folderPath', 'first');
      new TextInput ($div, 'folderPath', 'folderPath', $courseCode);

      $div = new Div ($listDiv, 'row');
      $coursePermissions = enumerateCourseContentPermissions ();
      new Label ($div, 'Folder Permissions:');
      $stack = new Div ($div, 'stack');
      new Hr ($stack);
      foreach ($coursePermissions as $flag => $desc) {
         $check = new Checkbox ($stack, 'accessFlags[]', NULL, $flag);
         if (strpos ($accessFlags, $flag) !== false) {
            $check->setAttribute ('checked', '1');
         }

         new TextEntity ($stack, $desc);
         new Br ($stack);
      }
      new Hr ($stack);

      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      if (! empty ($folder)) {
         new HiddenField ($this, "folderID", NULL, $folder->contentID);
      }
   }
}

?>
