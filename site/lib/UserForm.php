<?php

include_once "util/XMLEntity.php";
include_once "SiteConfig.php";

/*
 * UserForm: Presents a form with which to edit users.
 *
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

class UserForm extends Form {
   function __construct ($parent, $action, $userClass, $user = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'user_form');

      # Include the user validation script.
      new Script ($this, 'lib/user-form.js');

      $username = NULL;
      $externalID = NULL;
      $firstName = NULL;
      $middleInitial = NULL;
      $lastName = NULL;
      $emailAddress = NULL;
      $notes = NULL;
      $isActive = TRUE;
      $systemRole = 0;

      if (! empty ($user)) {
         $username = $user->username;
         $externalID = $user->externalID;
         $firstName = $user->firstName;
         $middleInitial = $user->middleInitial;
         $lastName = $user->lastName;
         $emailAddress = $user->emailAddress;
         $notes = $user->notes;
         $isActive = $user->isActive;
         $systemRole = $user->systemRole;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Username:', 'username', 'first');
      new TextInput ($div, 'username', 'username', $username);

      $div = new Div ($listDiv, 'row');
      $label = new Label ($div, NULL, 'external_id');
      new Para ($label, 'External ID:');
      new Para ($label, '(optional)', 'field_note');
      new TextInput ($div, 'externalID', 'externalID', $externalID);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'First Name:', 'firstName', 'first');
      new TextInput ($div, 'firstName', 'firstName', $firstName);
      
      new Label ($div, 'Middle Initial:', 'middleInitial');
      $input = new TextInput ($div, 'middleInitial', 'middleInitial', $middleInitial);
      $input->setAttribute ('maxlength', '1');
      $input->setAttribute ('style', 'width: 1em;');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Last Name:', 'lastName', 'first');
      new TextInput ($div, 'lastName', 'lastName', $lastName);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Email Address:', 'emailAddress', 'first');
      new TextInput ($div, 'emailAddress', 'emailAddress', $emailAddress);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'System Role:', 'systemRole');
      new Select ($div, 'systemRole', enumerateSystemRoles (), SYSTEM_ROLE_USER);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'User is Active?');
      $stack = new Div ($div, 'stack');
      new RadioButton ($stack, 'isActive', NULL, 1, $isActive == 1);
      new TextEntity ($stack, "Yes");
      new Br ($stack);
      new RadioButton ($stack, 'isActive', NULL, 0, $isActive == 0);
      new TextEntity ($stack, "No");
      new Br ($stack);
      new Hr ($stack);

      $listDiv = new Div ($fieldset, 'list');

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Notes:', 'notes', 'first top');
      new TextArea ($div, 'notes', 'notes', $notes);

      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      if (! empty ($user)) {
         new HiddenField ($this, "userID", NULL, $user->userID);
      }
   }
}

class UserEnrollmentForm extends Form {
   function __construct ($parent, $action, $course = NULL, $enrollment = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'user_form');

      # Include the user validation script.
      new Script ($this, 'lib/user.js');

      $this->setAttribute ('onSubmit', 'return userEnrollmentFormValidate (this);');
      
      $username = NULL;
      $courseCode = NULL;
      $courseRole = NULL;
      $accessFlags = NULL;

      if (! empty ($course)) {
         $courseCode = $course->courseCode;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Username:', 'username', 'first');
      new TextInput ($div, 'username', 'username', $username);
      
      if (! empty ($courseCode)) {
         new HiddenField ($this, 'courseCode', NULL, $courseCode);
      } else {
         $div = new Div ($listDiv, 'row');
         new Label ($div, 'Course Code:', 'courseCode');
         new TextInput ($div, 'courseCode', 'courseCode', $courseCode);
      }
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Course Role:', 'courseRole');
      new Select ($div, 'courseRole', enumerateCourseRoles (), COURSE_ROLE_STUDENT);
      
      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
      
      if (! empty ($user)) {
         new HiddenField ($this, "userID", NULL, $user->userID);
      }
   }
}


class UserSearchForm extends Form {
   function __construct ($parent, $action, $course = NULL)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'user_form');

      # Include the user validation script.
      new Script ($this, 'lib/user.js');

      $this->setAttribute ('onSubmit', 'return userSearchValidate (this);');

      $criterionArray = array (
         "Username",
         "Last Name",
         "Last, First"
      );
      
      $criterion = NULL;
      $search = NULL;

      if (! empty ($course)) {
         $courseCode = $course->courseCode;
      }

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Search by:', 'criterion', 'first');
      new Select ($div, 'criterion', $criterionArray, 0);
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Search with:', 'search');
      new TextInput ($div, 'search', 'search');
      
      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Search');
      new ResetButton ($div, 'Reset');

      if (! empty ($course)) {
         new HiddenField ($this, 'courseID', NULL, $course->courseID);
      }
   }
}

class UserSearchResults extends Div {
   function __construct ($parent, $authority, $users, $columnsBefore = array (),
      $columnsAfter = array ())
   {
      parent::__construct ($parent, 'search_results');

      $columns = array ('Username' => NULL, 'Name' => NULL);
      $columns = array_merge ($columnsBefore, $columns);
      $columns = array_merge ($columns, $columnsAfter);

      $table = new Table ($this, 'results');

      foreach (array_keys ($columns) as $column) {
         new TableHeader ($table->getHead (), $column);
      }

      foreach ($users as $user) {
         $row = new TableRow ($table->getBody ());

         foreach ($columns as $column => $param) {
            $this->createUserCell ($authority, $row, $column, $param, $user);
         }
      }
   }

   private function createUserCell ($authority, $row, $columnName, $param, $user)
   {
      switch ($columnName) {
      case "Username":
         $col = new TableColumn ($row);

         if ($authority->authorizeCheck ('viewProfile')) {
            new TextLink ($col, sprintf (
               "?action=viewProfile&username=%s", htmlentities ($user->username)),
            htmlentities ($user->username));
         } else {
            new TextEntity ($col, htmlentities ($user->username));
         }
         
         break;

      case "Name":
         $nameString = sprintf ("%s, %s", $user->lastName, $user->firstName);

         if (! empty ($user->middleInitial)) {
            $nameString = sprintf ("%s %s", $nameString, $user->middleInitial);
         }

         new TableColumn ($row, htmlentities ($nameString));
         break;

      case "Default":
         throw new CinciException ("Search Results Error",
            sprintf ("\"%s\" is an unknown search results column.", $columnName));
      }
   }
}

?>
