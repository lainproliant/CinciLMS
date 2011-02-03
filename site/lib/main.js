/*
 * main.js: Client-side initialization and setup routines.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 */

/*
 * Sets up the site upon initial loading.  Set as the 'onload' event 
 * of the window object.
 */
function windowLoad ()
{
   // Get a list of all input elements.
   var inputElements = document.getElementsByTagName ('input');
   
   // Set focus to the first input element if it exists.
   if (inputElements.length > 0) {
      inputElements[0].focus ();
   }
}

// Set an onload handler for the window.  This will cause the
// windowLoad ( ) method below to be called when the page loads.
window.onload = windowLoad;
