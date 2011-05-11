/*
 * itemform.js: Initialization of TinyMCE integration in content items.
 * 
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Depends on tiny_mce.js.
 */

tinyMCE.init ({
   theme: "advanced",
   mode: "exact",
   elements: "text",
   theme_advanced_toolbar_location: "top",
   theme_advanced_buttons1: "bold,italic,underline,strikethrough,separator,"
   + "justifyleft,justifyright,justifyfull,formatselect,bullist,numlist,outdent,indent",
   theme_advanced_buttons2: "link,unlink,anchor,image,separator,"
   + "undo,redo,cleanup,code,separator,sub,sup,charmap",
   theme_advanced_buttons3: "",
   height: "300px",
   width: "500px",
});

