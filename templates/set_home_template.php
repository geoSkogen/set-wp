<?php

class Set_Home_Template {

  public function __construct() {
    error_log('throw template');
  }

  public function app_html() {

    error_log('this is app the shell');
    ob_start();
    ?>
    <div id="appShell">
      <div id="headband" class=flexOuterSpace>
        <div id="settingsShell" class="iconShell">
          <i id="settingsIcon" class="fa">&#xf013;</i>
        </div>
        <div id="addRowShell" class="iconShell">
          <i id="addRowIcon" class="fa">&#xf067;</i>
        </div>
      </div>
      <div class="flexOuterCenter">
        <div id="app" class="flexOuterCenter">
        </div>
      </div>
      <div id=appModals>
        <div class="flexOuterCenter">
          <div id="panel" class="modal">
            <div class="relShell"><div class="closeModal">&times;</div></div>
            <div class="flexOuterCenter">This is the content.</div>
          </div>
        </div>
      </div>
    </div>
    <?php

    $html = ob_get_clean();

    return $html;
  }

}

?>
