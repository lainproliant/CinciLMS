<?php

/*
 * AJAXEntities: Special XMLEntities for AJAX request replies.
 *
 * (c) 2011 Lee Supe (lain_proliant)
 *
 * Released under the GNU General Public License, version 3.
 */

include_once 'util/XMLEntity.php';

class AJAXReply extends XMLEntity {
   function __construct () {
      parent::__construct (NULL, 'ajax-reply');
   }
}

class AJAXStatus extends XMLEntity {
   function __construct ($parent, $status) {
      parent::__construct ($parent, 'status');

      new TextEntity ($this, $status);
   }
}

class AJAXHeader extends XMLEntity {
   function __construct ($parent, $header) {
      parent::__construct ($parent, 'header');

      new TextEntity ($this, $header);
   }
}

class AJAXMessage extends XMLEntity {
   function __construct ($parent, $message) {
      parent::__construct ($parent, 'message');

      new TextEntity ($this, $message);
   }
}

?>
