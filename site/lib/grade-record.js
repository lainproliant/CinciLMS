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
 *    facebox.js,
 *    XMLEntity.js.
 */
var graderecord_grade_regex = new RegExp ("^-?[0-9]+(\.[0-9][0-9]?)?$");

var KEYCODE_ENTER = 13;
var KEYCODE_ESC   = 27;

var STATUS_READY = "Ready.";

$(document).ready (function () {
   
   /*
    * Set initial facebox opacity.
    */
   $.facebox.settings.opacity = 0.5;

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
      
      createColumnContextMenu (menuListXML, $(this).parent ().attr ('data-column'));
      
      /*
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
      */
      
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
   $("table.sortable td.editable").click (onGradeCellClick);
});

/*
 * Called when a grade cell is clicked.
 */
function onGradeCellClick (event) {
   // Create the text input for editing.
   var tableCell = $(this);

   // Mark the table cell as being edited so further click
   // events to not trigger this method.
   if (tableCell.attr ('editing') == undefined) {
      tableCell.attr ('editing', 'true');
   } else {
      return;
   }

   var textInput = $(X$ ('input').attr ('type', 'text').str ());

   var savedGrade = $.trim ($(this).text ());

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
      
      if (isValidGrade (newGrade)) {
         if (newGrade != savedGrade) {
            saveGrade (tableCell, savedGrade, parseFloat (newGrade));
            return;
         }
      } else {
         errorStatus ("Invalid grade expression.  A grade should be a positive or negative number with an optional 2-digit decimal component.", 2);
      }

      tableCell.text (savedGrade);
      tableCell.removeAttr ('editing');
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
}

/*
 * Attempts to save the given grade to the server.
 */
function saveGrade (tableCell, oldGrade, newGrade)
{
   var gradeCellIdentity = tableCell.attr ('data-cell');

   tableCell.append (X$ ('img').attr ('src', 'images/grade-submit.gif').str ());

   $.ajax ({
      type: "GET",

      url: sprintf ("ajax.php?action=saveGrade&cellIdentity=%s&grade=%s",
         gradeCellIdentity, newGrade),

      dataType: "xml",

      success: function (xml) {
         status = $(xml).find ('status').text ();
         message = $(xml).find ('message').text ();

         if (status == "success") {
            newGrade = $(xml).find ('grade').text ();

            successStatus (message, 2);
            tableCell.text (newGrade);
            $('table.sortable').trigger ('update');
            tableCell.removeAttr ('editing');

         } else if (status == "exception") {
            errorStatus (message, 5);
            tableCell.text (oldGrade);
            tableCell.removeAttr ('editing');
         
         } else {
            errorStatus (sprintf ("(%s): %s", status, message), 5);
            tableCell.text (oldGrade);
            tableCell.removeAttr ('editing');
         }

      },

      error: function (xmlHttpRequest, errorType, e) {
         errorStatus (sprintf ("AJAX Error (%s): %s", errorType, e)); 
         tableCell.text (oldGrade);
         tableCell.removeAttr ('editing');
      }
   });
}

/*
 * Updates the status div with an error message and resets after
 * a few seconds.
 */
function errorStatus (message, secs)
{
   statusDiv = $('#gradeRecordStatus');

   statusDiv.text (message);
   statusDiv.attr ('class', 'status');
   statusDiv.addClass ('errorStatus');

   statusDiv.stopTime ();

   statusDiv.oneTime (secs * 1000, function () {
      $(this).text (STATUS_READY);
      $(this).removeClass ('errorStatus');
   });
}

/*
 * Updates the status div with an error message and resets after
 * a few seconds.
 */
function successStatus (message, secs)
{
   statusDiv = $('#gradeRecordStatus');
   
   statusDiv.text (message);
   statusDiv.attr ('class', 'status');
   statusDiv.addClass ('successStatus');
   
   statusDiv.stopTime ();

   statusDiv.oneTime (secs * 1000, function () {
      $(this).text (STATUS_READY);
      $(this).removeClass ('successStatus');
   });
}

/*
 * Checks to see if the given string is a valid grade expression
 * and within the bounds of [MIN_GRADE, MAX_GRADE].
 */
function isValidGrade (grade)
{
   if (grade.match (graderecord_grade_regex)) {
      return true;
   } else {
      return false;
   }
}

/*
 * Creates a context menu for the given column.
 */
function createColumnContextMenu (menuListXML, columnIdentity)
{
   item = new Xc$ (menuListXML, 'li');
   
   actionLink = new Xc$ (item, 'a').attr (
         'href', sprintf ('javascript:showEditColumn("%s")',
            columnIdentity));
   new Tc$ (actionLink, 'Edit Column');
   
   actionLink = new Xc$ (item, 'a').attr (
         'href', sprintf ('javascript:showConfirmDeleteColumn("%s")',
            columnIdentity));
   new Tc$ (actionLink, 'Delete Column');
}

/*
 * Asks the server to provide a form to confirm deletion
 * of a column.
 */
function showConfirmDeleteColumn (columnIdentity)
{
   $.facebox ({ ajax: 
      sprintf (
         'contentLoad.php?action=confirmDeleteColumn&columnIdentity=%s',
         columnIdentity) });
}

/*
 * Prompts the user to create a new column.
 */
function showNewColumn (courseID)
{
   $.facebox ({ ajax:
      sprintf (
         'contentLoad.php?action=newColumn&courseID=%d',
         courseID) });
}

/*
 * Prompts the user to edit a column.
 */
function showEditColumn (columnIdentity)
{
   $.facebox ({ ajax:
      sprintf (
         'contentLoad.php?action=editColumn&columnIdentity=%s',
         columnIdentity) });
}

