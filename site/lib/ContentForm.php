<?php

/*
 * ContentForm: Forms for course content creation and editing. 
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";
include_once "Course.php";
include_once "Content.php";

class FolderForm extends Form {
   function __construct ($parent, $action, $userClass, $parentFolder, $folder = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'folder_form');

      # Include the content validation script.
      new Script ($this, 'lib/content.js');

      $this->setAttribute ('onSubmit', 'return folderFormValidate (this);');
      
      $folderName = NULL;
      $folderPath = NULL;
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;

      if (! empty ($folder)) {
         $folderName = $folder->name;
         $folderPath = $folder->pathName;
         $accessFlags = $folder->accessFlags;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Folder Name:', 'folderName', 'first');
      new TextInput ($div, 'folderName', 'folderName', $folderName);
      
      $div = new Div ($listDiv, 'row');
      $label = new Label ($div, NULL, 'folderPath');
      new Para ($label, 'Folder Path:');
      new Para ($label, '(optional)', 'field_note');
      new TextInput ($div, 'folderPath', 'folderPath', $folderPath);

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

      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      new HiddenField ($this, 'parent', NULL, $parentFolder->pathName); 
      new HiddenField ($this, 'contentType', NULL, 'folder');

      if (! empty ($folder)) {
         new HiddenField ($this, "folderID", NULL, $folder->contentID);
      }
   }
}

class ItemForm extends Form {
   function __construct ($parent, $action, $userClass, $parentFolder, $item = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'item_form');

      # Include the content validation script.
      new Script ($this, 'lib/content.js');
      
      # Include the tinymce and initializer scripts.
      new Script ($this, 'lib/util/tiny_mce/tiny_mce.js');
      new Script ($this, 'lib/itemform.js');

      $this->setAttribute ('onSubmit', 'return itemFormValidate (this);');
      
      // LRS-TODO: Title is deprecated and will be removed from schema.
      $title = NULL;
      $name = NULL;
      $text = NULL;
      $itemPath = NULL; 
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;

      if (! empty ($item)) {
         $name = $item->name;
         $text = $item->text;
         $accessFlags = $item->accessFlags;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Item Name:', 'folderName', 'first');
      new TextInput ($div, 'itemName', 'itemName', $name);
      
      $div = new Div ($listDiv, 'row');
      $label = new Label ($div, NULL, 'folderPath');
      new Para ($label, 'Folder Path:');
      new Para ($label, '(optional)', 'field_note');
      $stack = new Div ($div, 'stack');
      new TextInput ($stack, 'itemPath', 'itemPath', $itemPath);

      $listDiv = new Div ($fieldset, 'list');
      $div = new Div ($listDiv, 'row');
      $stack = new Div ($div, 'stack');
      new TextArea ($stack, 'text', 'text', $text);

      $listDiv = new Div ($fieldset, 'list');
      $div = new Div ($listDiv, 'row');
      $coursePermissions = enumerateCourseContentPermissions ();
      new Label ($div, 'Item Permissions:');
      $stack = new Div ($div, 'stack');
      foreach ($coursePermissions as $flag => $desc) {
         $check = new Checkbox ($stack, 'accessFlags[]', NULL, $flag);
         if (strpos ($accessFlags, $flag) !== false) {
            $check->setAttribute ('checked', '1');
         }

         new TextEntity ($stack, $desc);
         new Br ($stack);
      }

      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      new HiddenField ($this, 'parent', NULL, $parentFolder->pathName); 
      new HiddenField ($this, 'contentType', NULL, 'item');

      if (! empty ($item)) {
         new HiddenField ($this, "itemID", NULL, $item->contentID);
      }
   }
}

class AssignmentForm extends Form {
   function __construct ($parent, $action, $userClass, $parentFolder, $item = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'item_form');

      # Include the content validation script.
      new Script ($this, 'lib/content.js');
      
      # Include the tinymce and initializer scripts.
      new Script ($this, 'lib/util/tiny_mce/tiny_mce.js');
      new Script ($this, 'lib/itemform.js');

      $this->setAttribute ('onSubmit', 'return itemFormValidate (this);');
      
      // LRS-TODO: Title is deprecated and will be removed from schema.
      $title = NULL;
      $name = NULL;
      $text = NULL;
      $itemPath = NULL; 
      $accessFlags = COURSE_DEFAULT_PERMISSIONS;
      $pointsPossible = 100;
      $dueDate = NULL;

      if (! empty ($item)) {
         $name = $item->name;
         $text = $item->text;
         $accessFlags = $item->accessFlags;
         $pointsPossible = $item->pointsPossible;
         $dueDate = $item->dueDate;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Item Name:', 'folderName', 'first');
      new TextInput ($div, 'itemName', 'itemName', $name);
      
      $div = new Div ($listDiv, 'row');
      $label = new Label ($div, NULL, 'folderPath');
      new Para ($label, 'Folder Path:');
      new Para ($label, '(optional)', 'field_note');
      $stack = new Div ($div, 'stack');
      new TextInput ($stack, 'itemPath', 'itemPath', $itemPath);

      $listDiv = new Div ($fieldset, 'list');
      $div = new Div ($listDiv, 'row');
      $stack = new Div ($div, 'stack');
      new TextArea ($stack, 'text', 'text', $text);
      
      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      $label = new Label ($div, NULL, 'dueDate');
      new Para ($label, 'Due Date:');
      new Para ($label, '(MM/DD/YYYY)', 'field_note');
      $stack = new Div ($div, 'stack');
      new TextInput ($stack, 'dueDate', 'dueDate', $dueDate);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Points Possible:', 'pointsPossible', 'first');
      new TextInput ($div, 'pointsPossible', 'pointsPossible', $pointsPossible);

      $listDiv = new Div ($fieldset, 'list');
      $div = new Div ($listDiv, 'row');
      $coursePermissions = enumerateCourseContentPermissions ();
      new Label ($div, 'Item Permissions:');
      $stack = new Div ($div, 'stack');
      foreach ($coursePermissions as $flag => $desc) {
         $check = new Checkbox ($stack, 'accessFlags[]', NULL, $flag);
         if (strpos ($accessFlags, $flag) !== false) {
            $check->setAttribute ('checked', '1');
         }

         new TextEntity ($stack, $desc);
         new Br ($stack);
      }
      
      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');

      new HiddenField ($this, 'parent', NULL, $parentFolder->pathName); 
      new HiddenField ($this, 'contentType', NULL, 'assignment');

      if (! empty ($item)) {
         new HiddenField ($this, "itemID", NULL, $item->contentID);
      }
   }
}

?>
