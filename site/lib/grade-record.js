/*
 * grade-record.js: Sets up the grade record table for sorting,
 *    provides context menus, and powers AJAX features.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Requires 
 *    jquery.js, 
 *    jquery.tablesorter.js, 
 *    jquery.timers.js,
 *    XMLEntity.js.
 */

var KEYCODE_ENTER = 13;
var KEYCODE_ESC   = 27;

var MAX_GRADE_VAL = 9999999.99;
var MIN_GRADE_VAL = -9999999.99;

var STATUS_READY = "Ready.";

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
      var gradeSaved = false;

      $(this).empty ();

      textInput.val (savedGrade);
      textInput.width ($(this).width ());
      textInput.height ($(this).height ());
      
      /*
       * Hide the text input when it loses focus.
       */
      textInput.blur (function (event) {
         // If the grade has changed, try to save it to the server.
         var newGrade = $(this).val ();
         
         $(this).remove ();

         if (newGrade != savedGrade) {
            saveGrade (tableCell, savedGrade, newGrade);
         }
         
         $('table.sortable').trigger ('update');
      });

      /*
       * Save the text input when it is submitted (via Return key).
       */
      textInput.keyup (function (event) {
         switch (event.keyCode) {
         case KEYCODE_ENTER:
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
function saveGrade (tableCell, oldGrade, newGrade)
{
   var gradeCellIdentity = tableCell.attr ('data-cell');

   if (newGrade > MAX_GRADE_VAL || newGrade < MIN_GRADE_VAL) {
      statusError ("The grade value was out of bounds", 5);
      tableCell.text (oldGrade);
      return;
   }

   tableCell.text (newGrade);
   
   $.ajax ({
      type: "GET",
      url: sprintf ("ajax.php?action=saveGrade&cellIdentity=%s&grade=%s",
         gradeCellIdentity, newGrade),
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
   status = $(xml).find ('status').text ();
   message = $(xml).find ('message').text ();

   if (status == "success") {
      statusSuccess (message, 2);

   } else if (status == "exception") {
      statusError (message, 5);

   }
}

/*
 * Called when the AJAX_saveGrade request could not be completed.
 */
function onSaveGradeError (xmlHttpRequest, errorType, e)
{
   alert ("AJAX_saveGrade failed: " + errorType);
}

/*
 * Updates the status div with an error message and resets after
 * a few seconds.
 */
function statusError (message, secs)
{
   $('#gradeRecordStatus').text (message);
   $('#gradeRecordStatus').addClass ('errorStatus');

   $('#gradeRecordStatus').oneTime (secs * 1000, function () {
      $(this).text (STATUS_READY);
      $(this).removeClass ('errorStatus');
   });
}

/*
 * Updates the status div with an error message and resets after
 * a few seconds.
 */
function statusSuccess (message, secs)
{
   $('#gradeRecordStatus').text (message);
   $('#gradeRecordStatus').addClass ('successStatus');

   $('#gradeRecordStatus').oneTime (secs * 1000, function () {
      $(this).text (STATUS_READY);
      $(this).removeClass ('successStatus');
   });
}
