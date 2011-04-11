<?php

/*
 * XMLEntity: A PHP library for generating XML/XHTML markup. 
 * (c) 2010, 2011 Lee Supe
 * 
 * Released under the GNU General Public License, version 3.
 */

class XMLEntityException extends Exception { }

define ("XMLENTITY_INDENT_WIDTH", 3);

interface IStringRep {
   public function getString ();
}

/*
 * TextEntity: A generic printable text entity.
 */
class TextEntity implements IStringRep {
   private $text;

   function __construct ($parent, $text)
   {
      $this->text = $text;

      if (! empty ($parent)) {
         $parent->addChild ($this);
      }
   }
   
   public function getString ()
   {
      return $this->text;
   }
}

/*
 * XMLEntity: A generic XML document entity.
 */
class XMLEntity implements IStringRep {
   private $tag;
   private $attributes;
   private $children;
   protected $no_empty_tags;

   private $pretty;
   private $initial_il;

   // Constructs an XMLEntity.
   function __construct ($parent, $tag, $attributes = NULL)
   {
      $this->tag = $tag;
      $this->attributes = array ();
      $this->children = array ();
      $this->no_empty_tags = FALSE;

      if (! empty ($attributes)) {
         foreach ($attributes as $key => $value) {
            $this->setAttribute ($key, $value);
         }
      }

      if (! empty ($parent)) {
         $parent->addChild ($this);
      }
   }

   // Adds the given attribute to the entity.
   public function setAttribute ($attribute, $value)
   {
      // Validate the attribute name.
      if (ctype_alpha ($attribute[0]) and ctype_alnum ($attribute)) {
         $this->attributes[$attribute] = $value;
      } else {
         throw new XMLEntityException (
            "Invalid attribute name: \'$attribute\'.  Must be alphanumeric and begin with an alpha character."
         );
      }
   }

   // Adds a child node to the entity.
   public function addChild ($child)
   {
      array_push ($this->children, $child);
   }

   // Gets a list of child nodes.
   public function getChildren ()
   {
      return $this->children;
   }

   /*
    * Gets a string representation of the entity.
    */
   public function getString ()
   {
      return $this->generateString ($this->pretty, $this->initial_il);
   }

   /*
    * Returns a representation of the entire entity with 
    * child nodes as a string
    *
    * pretty:     Determine whether output should be pretty and indented.
    *             Default is FALSE.
    * il:         The initial indent level.  Also used recursively by
    *             the pretty printing code.
    *             Default is 0.
    */
   public function generateString ($pretty = FALSE, $il = 0)
   {
      $html = '';
      
      if ($pretty) {
         $html .= str_repeat (' ', XMLENTITY_INDENT_WIDTH * $il) . '<' . $this->tag;
      } else {
         $html .= '<' . $this->tag;
      }

      foreach ($this->attributes as $key => $value) {
         $html .= ' ' . $key . '=\'' . $value . '\'';
      }

      if (! empty ($this->children) or $this->no_empty_tags) {
         $do_newline = FALSE;
         $html .= '>';

         foreach ($this->children as $child) {
            if (get_class ($child) != "TextEntity") {
               if ($pretty) {
                  $html .= "\n" . $child->generateString ($pretty, $il + 1);
                  $do_newline = TRUE;
               } else {
                  $html .= $child->generateString ($pretty, $il);
               }

            } else {
               $html .= $child->getString ();
            }
         }
         
         if ($pretty && $do_newline) {
            $html .= "\n" . str_repeat (' ', XMLENTITY_INDENT_WIDTH * $il) . '</' . $this->tag . '>';
         } else {
            $html .= '</' . $this->tag . '>';
         }
      } else {
         $html .= "/>";
      }

      return $html;
   }

   /*
    * Sets pretty print options.
    *
    * pretty:     Whether the output should be indented pretty-like.
    * initial_il: The initial indent level of the text.
    *             By default, this is 0.
    */
   public function setPrettyPrint ($pretty, $initial_il = 0)
   {
      $this->pretty = $pretty;
      $this->initial_il = $initial_il;
   }

   /*
    * Prints a representation of the entire entity with 
    * child nodes as a string
    */
   public function printString ()
   {
      print $this->getString ();
      print "\n";
   }
}

/*
 * Anchor: A convenience class for an anchor within a page.
 */
class Anchor extends XMLEntity {
   function __construct ($parent, $name)
   {
      parent::__construct ($parent, 'a', array (
         'name' => $name));
   }
}

/*
 * Hyperlink: A convenience class for a link to a page.
 */
class Hyperlink extends XMLEntity {
   function __construct ($parent, $href, $text = NULL)
   {
      parent::__construct ($parent, 'a', array (
         'href' => $href));

      if (! empty ($text)) {
         new TextEntity ($this, $text);
      }
   }
}

/*
 * TextLink: A type of hyperlink containing only text.
 * Hyperlink and TextLink are the same, except that
 * TextLink will contain the text of the href if other
 * text is not specified.
 */
class TextLink extends Hyperlink {
   function __construct ($parent, $href, $text = NULL)
   {
      if (empty ($text)) {
         $text = $href;
      }

      parent::__construct ($parent, $href, $text);
   }
}

/*
 * Div: A convenience class for defining a div element.
 */
class Div extends XMLEntity {
   function __construct ($parent, $class = NULL)
   {
      parent::__construct ($parent, "div");

      if (! empty ($class)) {
         $this->setAttribute ("class", $class);
      }

      // For some reason, browsers hate XML-style empty <div/> tags.
      // By default, have an empty div print as <div></div> instead of <div/>.
      $this->no_empty_tags = TRUE;
   }
}

/*
 * Form: A convenience class for an HTML form.
 */
class Form extends XMLEntity {
   function __construct ($parent, $action, $method, $class = NULL) {
      parent::__construct ($parent, 'form', array (
         'method'=>$method));
   
      if (! empty ($action)) {
         $this->setAttribute ('action', $action);
      }

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Label: A convenience class for defining a label.
 */
class Label extends XMLEntity {
   function __construct ($parent, $text, $forid = NULL, $class = NULL)
   {
      parent::__construct ($parent, "label");

      new TextEntity ($this, $text);

      if (! empty ($forid)) {
         $this->setAttribute ('for', $forid);
      }

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Input: An abstract class for input items.
 */
class Input extends XMLEntity {
   function __construct ($parent, $type, $name, $id = NULL, $value = NULL)
   {
      parent::__construct ($parent, "input", array (
         'type'=>$type));

      if (! empty ($name)) {
         $this->setAttribute ('name', $name);
      }
      if (! empty ($id)) {
         $this->setAttribute ('id', $id);
      }

      if (! is_null ($value)) {
         $this->setAttribute ('value', $value);
      }
   }
}

/*
 * TextInput: A convenience class for single-line text input.
 */
class TextInput extends Input {
   function __construct ($parent, $name, $id = NULL, $value = NULL)
   {
      parent::__construct ($parent, 'text', $name, $id, $value);
   }
}

/*
 * PasswordInput: A convenience class for password input.
 */
class PasswordInput extends Input {
   function __construct ($parent, $name, $id = NULL, $value = NULL)
   {
      parent::__construct ($parent, 'password', $name, $id, $value);
   }
}

/*
 * HiddenField: A convenience class for a hidden form field.
 */
class HiddenField extends Input {
   function __construct ($parent, $name, $id = NULL, $value = NULL)
   {
      parent::__construct ($parent, 'hidden', $name, $id, $value);
   }
}

/*
 * RadioButton: A convenience class for a radio button.
 */
class RadioButton extends Input {
   function __construct ($parent, $name, $id, $value, $checked = FALSE) {
      parent::__construct ($parent, 'radio', $name, $id, $value);

      if ($checked) {
         $this->setAttribute ('checked', '1');
      }
   }
}

/*
 * Checkbox: A convenience class for a check box.
 */
class Checkbox extends Input {
   function __construct ($parent, $name, $id, $value, $checked = FALSE) {
      parent::__construct ($parent, 'checkbox', $name, $id, $value);

      if ($checked) {
         $this->setAttribute ('checked', '1');
      }
   }
}

/*
 * ActionButton: A convenience class for a button link to execute script.  Requires JavaScript.
 */
class ActionButton extends Input {
   function __construct ($parent, $label, $action, $class)
   {
      parent::__construct ($parent, 'button', NULL);

      $this->setAttribute ('value', $label);
      $this->setAttribute ('onClick', $action);

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Button: A convenience class for a form action button.
 */
class Button extends XMLEntity {
   function __construct ($parent, $type, $label)
   {
      parent::__construct ($parent, 'button', array (
         'type'=>$type));

      new TextEntity ($this, $label);
   }
}

/*
 * SubmitButton: A convenience class for a submit button.
 */
class SubmitButton extends Button {
   function __construct ($parent, $label)
   {
      parent::__construct ($parent, 'submit', $label);
   }
}

/*
 * ResetButton: A convenience class for a reset button.
 */
class ResetButton extends Button {
   function __construct ($parent, $label)
   {
      parent::__construct ($parent, 'reset', $label);
   }
}

/*
 * TextArea: A convenience class for a text area.
 */
class TextArea extends XMLEntity {
   function __construct ($parent, $name, $id = NULL, $value = NULL)
   {
      parent::__construct ($parent, 'textarea', array (
         'name' => $name));

      if (! empty ($id)) {
         $this->setAttribute ('id', $id);
      }

      if (! empty ($value)) {
         new TextEntity ($this, htmlentities ($value));
      }

      $this->no_empty_tags = TRUE;
   }
}

/*
 * Select: A convenience class for a select box.
 */
class Select extends XMLEntity {
   function __construct ($parent, $name, $options, $default = NULL)
   {
      parent::__construct ($parent, 'select', array (
         'name'      => $name));

      foreach ($options as $value => $text) {
         $option = new XMLEntity ($this, 'option', array (
            'value' => $value));

         new TextEntity ($option, $text);

         if ($value == $default) {
            $option->setAttribute ('selected', 'selected');
         }
      }
   }
}

/*
 * MonthSelect: A convenience class for a month selector.
 */
class MonthSelect extends Select {
   function __construct ($parent, $name, $default = NULL)
   {
      parent::__construct ($parent, $name, array (
         '1'      => 'January',
         '2'      => 'February',
         '3'      => 'March',
         '4'      => 'April',
         '5'      => 'May',
         '6'      => 'June',
         '7'      => 'July',
         '8'      => 'August',
         '9'      => 'September',
         '10'     => 'October',
         '11'     => 'November',
         '12'     => 'December'), $default);
   }
}

/*
 * FieldSet: A convenience class for a fieldset/legend pair.
 */
class FieldSet extends XMLEntity {
   function __construct ($parent, $class = NULL, $legend = NULL)
   {
      parent::__construct ($parent, 'fieldset');

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }

      if (! empty ($legend)) {
         $legendTag = new XMLEntity ($this, 'legend');
         new TextEntity ($legendTag, $legend);
      }
   }
}

/*
 * Br: A convenience class for a line break.
 */
class Br extends XMLEntity {
   function __construct ($parent)
   {
      parent::__construct ($parent, 'br');
   }
}

/*
 * Hr: A convenience class for a horizontal rule.
 */
class Hr extends XMLEntity {
   function __construct ($parent)
   {
      parent::__construct ($parent, 'hr');
   }
}

/*
 * UnorderedList: An unordered list of elements.
 * To use this list, create HTMLEntities with NULL parents,
 * then construct an UnorderedList and add the items in order,
 * either via the constructor or with the addChild method.
 */
class UnorderedList extends XMLEntity {
   function __construct ($parent, $class = NULL, $children = NULL)
   {
      parent::__construct ($parent, 'ul');

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }

      if (! empty ($children)) {
         foreach ($children as $child) {
            $this->addListItem ($child);
         }
      }
   }

   function addListItem ($child) 
   {
      $li = new ListItem ($this); 
      $li->addChild ($child);
      return $li;
   }
}

/*
 * ListItem: Convenience function for a list item.
 */
class ListItem extends XMLEntity {
   function __construct ($parent)
   {
      parent::__construct ($parent, 'li');
   }
}

/*
 * Table: A convenience class for a table.
 */
class Table extends XMLEntity {
   function __construct ($parent, $class = NULL)
   {
      parent::__construct ($parent, 'table');

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }

   // Sets the class of rows in the table alternating through the
   // provided classes.  Use this to create alternating rows,
   // but only once the table has been populated with rows.
   function alternateRows ($classes = array ('r1', 'r2'))
   {
      $classID = 0;

      foreach ($this->getChildren () as $childNode) {
         if (get_class ($childNode) == "TableRow") {
            $childNode->setAttribute ('class', $classes[$classID]);
            $classID = ($classID + 1) % sizeof ($classes);
         }
      }
   }
}

/*
 * TableHeader: A convenience class for a table header.
 */
class TableHeader extends XMLEntity {
   function __construct ($parent, $text, $class = NULL)
   {
      parent::__construct ($parent, 'th');

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }

      new TextEntity ($this, $text);
   }
}

/*
 * TableRow: A convenience class for a table row.
 */
class TableRow extends XMLEntity {
   function __construct ($parent, $class = NULL)
   {
      parent::__construct ($parent, 'tr');

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * TableColumn: A conveninece class for a table column.
 */
class TableColumn extends XMLEntity {
   function __construct ($parent, $text = NULL, $class = NULL)
   {
      parent::__construct ($parent, 'td');

      if (! empty ($text)) {
         new TextEntity ($this, $text);
      }

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Span: A convenience class for a text span.
 */
class Span extends XMLEntity {
   function __construct ($parent, $text, $class = NULL)
   {
      parent::__construct ($parent, 'span');

      new TextEntity ($this, $text);

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Para: A convenience class for a paragraph.
 */
class Para extends XMLEntity {
   function __construct ($parent, $text, $class = NULL)
   {
      parent::__construct ($parent, 'p');

      new TextEntity ($this, $text);

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Image: A convenience class for an image.
 */
class Image extends XMLEntity {
   function __construct ($parent, $src, $alt, $class = NULL)
   {
      parent::__construct ($parent, 'img');

      $this->setAttribute ('src', $src);
      $this->setAttribute ('alt', $alt);

      if (! empty ($class)) {
         $this->setAttribute ('class', $class);
      }
   }
}

/*
 * Script: A convenience class for an inline <script> tag.
 */
class Script extends XMLEntity {
   function __construct ($parent, $src, $type = 'text/javascript') {
      parent::__construct ($parent, 'script');

      $this->setAttribute ('type', $type);
      $this->setAttribute ('src', $src);
      
      // Browsers also dislike empty <script> tags.
      $this->no_empty_tags = TRUE;
   }
}

/*
 * xml_header: A utility function to print an XML header.
 */
function xml_header ($version = "1.0", $encoding = "UTF-8")
{
   print sprintf ("<?xml version=\"%s\" encoding=\"%s\" ?>\n", $version, $encoding);
}

/*
 * Replace all instances of any non-alphanumeric or 
 * underscore character with an underscore.
 * Useful for limiting the characters in course
 * content path names.
 *
 * $string:    The string to filter.
 *
 * Returns a filtered version of the string.
 */
function anumfilter ($string)
{
   return preg_replace ('/[^A-Za-z0-9_-]/', '_', $string);
}


?>
