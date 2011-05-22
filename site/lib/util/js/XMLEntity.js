/*
 * XMLEntity.js: A simple XML/XHTML construction framework.
 *    A simplified version of XMLEntity based on XMLEntity.php.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 * Released under the GNU General Public License, version 3
 *
 * Requires jquery.js and sprintf.js.
 */

var xmlentity_attribute_regex = new RegExp ("^[A-Za-z_:][A-Za-z0-9_\\-.:]+$");
/**
 * Creates an XMLEntity object.
 *
 * @param parent
 *    The parent XMLEntity object, or null if there is no parent.
 *
 * @param tag
 *    The entity's tag.
 *
 * @param attrs
 *    An optional associative array of attributes, default is an empty list.
 */
function XMLNode (tag, attrs) {

   attrs = typeof (attrs) == 'undefined' ? {} : attrs;

   setAttr = function (attribute, value)
   {
      if (isValidAttributeName (attribute)) {
         this.attrs [attribute] = value.toString ();

      } else {
         throw new Error (sprintf ("Invalid attribute name: %s", attribute));
      }

      return this;
   };

   addChild = function (child) {
      this.children.push (child);

      return this;
   };

   setClass = function (class) {
      this.attr ('class', class);

      return this;
   }

   toString = function () {
      var html = "";

      html += '<' + this.tag;

      $.each (this.attrs, function (attr, value) {
         html += ' ' + attr + "='" + value + "'";
      });

      if (this.children.length > 0 || this.no_empty_tags) {
         html += ">";

         $.each (this.children, function (idx, child) {
            html += child.toString ();
         });

         html += '</' + this.tag + '>';

      } else {
         html += "/>";
      }

      return html;
   };
   
   this.tag = tag;
   this.attrs = attrs;
   this.children = [];
   this.no_empty_tags = false;
   
   this.attr = setAttr;
   this.class = setClass;
   this.add = addChild;
   this.str = toString;
   this.toString = toString;

   return this;
}

function XMLChildNode (parent, tag, attrs)
{
   var node = new XMLNode (tag, attrs);
   parent.add (node);

   return node;
}

/**
 * Creates a text entity.
 */
function TextNode (text) {
  
   toString = function () {
      return this.text;
   }

   this.text = text;

   this.str = toString;
   this.toString = toString;
   
   return this;
}

/**
 * Creates a child text entity for an XMLEntity.
 */
function TextChildNode (parent, text)
{
   var node = new TextNode (text);
   parent.add (node);

   return node;
}


X$ = XMLNode;
Xc$ = XMLChildNode;
T$ = TextNode;
Tc$ = TextChildNode;

/**
 * Checks to see if the given string is a valid attribut name.
 */
function isValidAttributeName (attribute)
{
   return attribute.match (xmlentity_attribute_regex);
}

