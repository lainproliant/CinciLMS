/*
 * grade-record.js: Sets up the grade record table for sorting,
 *    provides context menus, and powers AJAX features.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Requires jquery.js, jquery.tablesorter.js, and XMLEntity.js.
 */

$(document).ready (function () {
   $("table.sortable").tablesorter ({
      widgets: ['zebra']
   });

   $("#column-context-menu").hide ();

   $("#column-context-menu").mouseleave (function (event) {
      $(this).fadeOut ();
   });

   $("table.sortable th img").click (function (event) {
      var columnContextMenu = $("#column-context-menu");
      var x = event.pageX - 8, y = event.pageY - 8;

      columnContextMenu.css ({
         top: y,
         left: x
      });
      
      menuListXML = new X$ ('ul').class ('L1 context');

      for (var n = 0; n < 5; n++) {
         item = new Xc$ (menuListXML, 'li').add (
            new X$ ('p').add (
               new T$ (sprintf ("Item %d", n))));

         subMenu = new Xc$ (item, 'ul').class ('L2');

         for (var m = 0; m < 5; m++) {
            new Xc$ (subMenu, 'li').add (
               new X$ ('p').add (
                  new T$ (sprintf ("Subitem %d", m))));
         }
      }
      
      alert (menuListXML);

      columnContextMenu.empty ();
      columnContextMenu.append ($(menuListXML.toString ()));
      columnContextMenu.show ();

      event.stopPropagation ();
   });

   $("table.sortable td.editable").dblclick (function (event) {
      
   });

});

