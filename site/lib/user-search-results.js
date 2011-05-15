/*
 * user-search-results.js: Sets up the user search results table for sorting
 *    and provides context menus.
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
});

