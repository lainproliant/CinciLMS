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
