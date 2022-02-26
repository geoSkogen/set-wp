<?php

class Set_Default_Template {

  public function __construct() {
    error_log('throw template');
  }

  public function app_html() {

    error_log('this is the error shell');
    ob_start();
    ?>
    <h3> a bell is a cup - until it is struck</h3>
    <?php

    $html = ob_get_clean();

    return $html;
  }

}

?>
