/*
 * login.js: Login and password change validation for the
 *           Cincinnatus Learning Management System.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Depends on jssha256.js and sprintf-0.6.js
 */

// Enable this to show some debug messages.
const CINCI_DEBUG = false;

var regexUsername = new RegExp ('^[A-Za-z0-9_]*$');

/* 
 * Validates the user login form.  Set as the 'onSubmit' method.
 */
function loginFormValidate (loginForm)
{
   // Validate the username.  Should be a series of word characters with no spaces.
   if (! regexUsername.test (loginForm.username.value)) {
      alert ('Invalid username.  Should not contain spaces or other special characters.');
      return false;
   }

   // Generate the sha256 hex digest of the password field.
   // One should not rely simply on client-side hashing as a security technique.
   // It only serves as a small obfuscation of the password, as is still
   // susceptible to replay attacks.  I do personaly believe however that if a
   // password is to be transmitted over unsecured HTTP, it should be
   // transmitted in a hashed form as to make reuse of the intercepted password 
   // on other services much more difficult.
   //
   // In other words, this is not an alternative to a secure SSL login and
   // should be used along with SSL in practice.
   SHA256_init ();
   SHA256_write (loginForm.salt.value + loginForm.password.value);
   digest = SHA256_finalize ();
   hex_digest = array_to_hex_string (digest);
   
   // Null the salt so we don't post it unnecessarily.
   loginForm.salt.value = "";
   
   // Set the value of the password field to contain
   // the salted password hash.
   loginForm.password.value = hex_digest;

   if (CINCI_DEBUG) {
      alert (sprintf ("SHA256 Password Digest: %s", loginForm.password.value));
   }

   return true;
}

/* 
 * Validates the change password form.  Set as the 'onSubmit' method.
 */
function changePasswordFormValidate (loginForm)
{
   // Verify that the two passwords match.
   if (loginForm.new_password_A.value != loginForm.new_password_B.value) {
      alert ("The passwords do not match.  Please try again.");
      loginForm.new_password_A.focus ();
      return false;
   }
   
   // Generate the sha256 hex digests of the old and new passwords.
   SHA256_init ();
   SHA256_write (loginForm.salt.value + loginForm.old_password.value);
   digest = SHA256_finalize ();
   hex_digest = array_to_hex_string (digest);
   loginForm.old_password.value = hex_digest;
   
   SHA256_init ();
   SHA256_write (loginForm.salt.value + loginForm.new_password_A.value);
   digest = SHA256_finalize ();
   hex_digest = array_to_hex_string (digest);
   loginForm.new_password_A.value = hex_digest;

   // Null the salt and new_password_B so we don't post them unnecessarily.
   loginForm.salt.value = "";
   loginForm.new_password_B.value = "";
   
   return true;
}

