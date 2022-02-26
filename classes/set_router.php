<?php

class Set_Router {

  public $subdomain;
  protected $templates_path;

  public function __construct($subdomain) {
    $this->subdomain = $subdomain;
    $this->templates_path = __DIR__ . '../../templates/';
  }

  public function get($uri) {

    $resource = str_replace($this->subdomain,'',$uri);

    switch($resource) {

      case '/' :

        if (!class_exists('Set_Home_Template')) {
          include_once $this->templates_path . 'set_home_template.php';
        }
        $app_html = new Set_Home_Template();
        break;

      default :
        if (!class_exists('Set_Default_Template')) {
          include_once $this->templates_path . 'set_default_template.php';
        }
        $app_html = new Set_Default_Template();
    }

    return $app_html->app_html();

  }
}

?>
