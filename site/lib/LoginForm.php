<?php

include_once "util/XMLEntity.php";

/*
 * LoginForm: The login form and password reset forms.
 *
 * (c) 2011 Lee Supe 
 * Releaseed under the GNU General Public License, version 3.
 */

class LoginForm extends Form {
   function __construct ($parent, $action)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'login');
      
      # Include the login validation script.
      new Script ($this, 'lib/login.js');

      $this->setAttribute ('onSubmit', 'return loginFormValidate (this);');

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, "Username:", "username");
      new TextInput ($div, 'username', 'username');
       
      $div = new Div ($listDiv, 'row');
      new Label ($div, "Password:", "password");
      new PasswordInput ($div, 'password', 'password');
   
      $div = new Div ($listDiv, 'row submit_row');
      new Label ($div, "&nbsp;");
      new SubmitButton ($div, "Login");
      new ResetButton ($div, "Reset");

      new HiddenField ($this, "salt", NULL, $SiteConfig['db']['static_salt']);
   }
}

class ChangePasswordForm extends Form {
   function __construct ($parent, $action)
   {
      global $SiteConfig;

      parent::__construct ($parent, $action, 'POST', 'login');

      # Include the password change validation script.
      new Script ($this, 'lib/login.js');

      $this->setAttribute ('onSubmit', 'return changePasswordFormValidate (this);');

      $fieldset = new FieldSet ($this);

      $listDiv = new Div ($fieldset, 'list');
      
      $div = new Div ($listDiv, 'row');
      new Label ($div, "Password:", "old_password");
      new PasswordInput ($div, 'old_password', 'old_password');
   
      $div = new Div ($listDiv, 'row');
      new Label ($div, "New:", "new_password_A");
      new PasswordInput ($div, 'new_password_A', 'new_password_A');
      $div = new Div ($listDiv, 'row');
      
      new Label ($div, "Confirm New:", "new_password_B");
      new PasswordInput ($div, 'new_password_B', 'new_password_B');

      $div = new Div ($listDiv, 'row submit_row');

      new Label ($div, "&nbsp;");
      new SubmitButton ($div, "Change Password");
      new ResetButton ($div, "Reset");

      new HiddenField ($this, "salt", NULL, $SiteConfig['db']['static_salt']);
   }
}

?>
