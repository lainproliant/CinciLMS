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
      new Script ($this, 'lib/user.js');

      $this->setAttribute ('onSubmit', 'return userFormValidate (this);');
      
      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
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

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Username:', 'username', 'first');
      new TextInput ($div, 'username', 'username', $username);

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'ExternalID:', 'external_id', 'first');
      new TextInput ($div, 'externalID', 'externalID', $externalID);
      new Span ($div, '(optional)', 'field_note');

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
      $listDiv = new Div ($fieldset, 'list');
      $systemRoles = enumerateSystemRoles ();
      new Label ($div, 'System Role:');
      $stack = new Div ($div, 'stack');
      foreach ($systemRoles as $roleID => $desc) {
         $radio = new RadioButton ($stack, 'systemRole', NULL, $roleID);
         new TextEntity ($stack, $desc);
         new Br ($stack);
      }

      $div = new Div ($listDiv, 'row');
      new Label ($div, 'Notes:', 'notes', 'first top');
      new TextArea ($div, 'notes', 'notes', $notes);

      $div = new Div ($listDiv, 'row');
      new Label ($div, '&nbsp;');
      new SubmitButton ($div, 'Submit');
      new ResetButton ($div, 'Reset');
   }
}

?>
