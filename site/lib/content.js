/*
 * content.js:  Useful javascript for viewing and manipulating content.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3.
 *
 * Requires
 *    jquery.js,
 *    jquery-ui.min.js,
 *    facebox.js,
 *    XMLEntity.js.
 */
$(document).ready (function () {
   
   $("ul.sortable").sortable ({
      opacity: 0.5,
      cursor: 'move',
      update: onContentSort
   });

   $("img.context-menu").click (function (event) {
      var itemContextMenu = $("#item-context-menu");
      
      var x = event.pageX - 8, y = event.pageY - 8;

      itemContextMenu.css ({
         top: y,
         left: x
      });
      
      menuListXML = new X$ ('ul').class ('L1 context');
      
      createItemContextMenu (menuListXML, $(this).attr ('data-content'));
      
      itemContextMenu.empty ();
      itemContextMenu.append ($(menuListXML.toString ()));
      itemContextMenu.show ();
   });

   $("#item-context-menu").mouseleave (function (event) {
      $(this).fadeOut ();
   });

   $("#item-context-menu").hide ();

});

function onContentSort () {

   $.ajax ({
      type: "POST",

      url: sprintf ("ajax.php?action=submitContentSortOrder&path=%s",
         $(this).attr ("data-path")),

      dataType: "xml",
      
      data: $(this).sortable ("serialize"),

      success: function (xml) {
         status = $(xml).find ('status').text ();
         message = $(xml).find ('message').text ();
         
         if (status == 'exception') {
            $.facebox (createWarningXML ("Content Sort Error",
                  sprintf ("There was a problem updating the sort order of the content in this folder: %s",
                     message)))
         }
      },

      error: function (xmlHttpRequest, errorType, e) {
         $.facebox (createWarningXML ("Content Sort Error", sprintf (
               "There was an AJAX error while updating the sort order of the content in this folder: %s",
               e)));
      }
   });
}

function createWarningXML (header, message)
{
   div = X$ ('div').class ('warning prompt');
   
   Tc$ (Xc$ (div, 'h3'), header);
   Tc$ (Xc$ (div, 'p'), message);
   
   return div.str ();
}

/*
 * Creates a context menu for the given content item.
 */
function createItemContextMenu (menuListXML, contentID)
{
   item = new Xc$ (menuListXML, 'li');
   
   actionLink = new Xc$ (item, 'a').attr (
         'href', sprintf ('?action=editContent&columnID=%s',
            contentID));
   new Tc$ (actionLink, 'Edit Content');
   
   actionLink = new Xc$ (item, 'a').attr (
         'href', sprintf ('javascript:showConfirmDeleteContent(%s)',
            contentID));
   new Tc$ (actionLink, 'Delete Content');
}

/*
 * Asks the server to provide a form to confirm deletion
 * of a column.
 */
function showConfirmDeleteContent (contentID)
{
   $.facebox ({ ajax: 
      sprintf (
         'contentLoad.php?action=confirmDeleteContent&contentID=%s',
         contentID) });
}
