/*
 * grade-record.js: Sets up the grade record table for sorting,
 *    provides context menus, and powers AJAX features.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Requires jquery.js and jquery.tablesorter.js plugin.
 */

$(document).ready (function () {
   $("table.sortable").tablesorter ({
      widgets: ['zebra']
   });

   $("table.sortable th img").click (function (event) {
      alert ("Clicked me!");
      event.stopPropagation ();
   });

});

