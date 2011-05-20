/*
 * grade-record.js: Sets up the grade record table for sorting,
 *    provides context menus, and powers AJAX features.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Requires jquery.js, jquery.tablesorter.js, and XMLEntity.js.
 */

var KEYCODE_ENTER = 13;
var KEYCODE_ESC   = 27;

$(document).ready (function () {
   /*
    * Applies the tablesorter to the grade record table
    * and enables dynamic zebra striping.
    */
   $("table.sortable").tablesorter ({
      widgets: ['zebra']
   });

   /*
    * Shows the context menu for a column when its
    * menu icon is clicked.
    */
   $("table.sortable th img").click (function (event) {
      var columnContextMenu = $("#column-context-menu");
      var x = event.pageX - 8, y = event.pageY - 8;

      columnContextMenu.css ({
         top: y,
         left: x
      });
      
      menuListXML = new X$ ('ul').class ('L1 context');

      for (var n = 0; n < 5; n++) {
         item = new Xc$ (menuListXML, 'li');
         header = new Xc$ (item, 'p').add (
               new T$ (sprintf ("Item %d", n)));

         new Xc$ (header, 'img').attr (
            'src', 'images/menu-right.png').attr (
               'alt', 'submenu');

         subMenu = new Xc$ (item, 'ul').class ('L2');

         for (var m = 0; m < 5; m++) {
            new Xc$ (subMenu, 'li').add (
               new X$ ('p').add (
                  new T$ (sprintf ("Subitem %d", m))));
         }
      }
      
      columnContextMenu.empty ();
      columnContextMenu.append ($(menuListXML.toString ()));
      columnContextMenu.show ();

      event.stopPropagation ();
   });
   
   /*
    * Hides the context menu when the mouse leaves it.
    */
   $("#column-context-menu").mouseleave (function (event) {
      $(this).fadeOut ();
   });
   
   /*
    * Hides the context menu initially.
    */
   $("#column-context-menu").hide ();

   /*
    * Begins editing of an editable cell when it is double clicked.
    */
   $("table.sortable td.editable").dblclick (function (event) {
      // Create the text input for editing.
      var tableCell = $(this);
      var textInput = $(X$ ('input').attr ('type', 'text').toString ());

      var savedGrade = $.trim ($(this).text ());
      var newGrade = savedGrade;

      $(this).empty ();

      textInput.val (savedGrade);
      textInput.width ($(this).width ());
      textInput.height ($(this).height ());
      
      /*
       * Hide the text input when it loses focus.
       */
      textInput.blur (function (event) {
         // If the grade has changed, try to save it to the server.
         if (newGrade != savedGrade) {
            metadata = tableCell.attr ('data-cell');
            saveGrade (metadata, newGrade);
         }
         
         tableCell.text (newGrade);
         $(this).remove ();
      });

      /*
       * Save the text input when it is submitted (via Return key).
       */
      textInput.keyup (function (event) {
         switch (event.keyCode) {
         case KEYCODE_ENTER:
            newGrade = $(this).val ();
            $(this).blur ();
            break;
         case KEYCODE_ESC:
            $(this).blur ();
            break;
         }
      });
     

      $(this).append (textInput); 
      
      textInput.focus ();
      textInput.select ();
   });
});


/*
 * Attempts to save the given grade to the server.
 */
function saveGrade (gradeCellIdentity, grade)
{
   alert (sprintf ("Attempting to save \"%s\" with grade \"%s\".",
            gradeCellIdentity,
            grade));

   

   $.ajax ({
      type: "GET",
      url: sprintf ("ajax.php?action=saveGrade&cellIdentity=%s&grade=%s",
         gradeCellIdentity, grade),
      dataType: "xml",
      success: onSaveGradeReply,
      error: onSaveGradeError
   });
}

/*
 * Called when the AJAX_saveGrade request completes successfully.
 */
function onSaveGradeReply (xml)
{
   // Get the request status.
   var status = $(xml).find ('status').text ();

   if (status == 'exception') {

      
   }

   alert ("AJAX_saveGrade reply: " + $(xml).find ('status').text ());
}

/*
 * Called when the AJAX_saveGrade request could not be completed.
 */
function onSaveGradeError (xmlHttpRequest, errorType, e)
{
   alert ("AJAX_saveGrade failed: " + errorType);
}

