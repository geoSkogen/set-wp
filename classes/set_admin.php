<?php

class Set_Admin {

  protected $options;
  protected $suboptions;

  public $style_handles;
  public $script_handles;

  public function __construct($script_handles,$style_handles) {

    $this->style_handles = $style_handles;
    $this->script_handles = $script_handles;

    //
    add_action(
     'admin_menu',
     [$this,'set_register_options_pages']
    );
    //
    add_action(
      'admin_init',
      [$this,'set_init_settings_api']
    );
    //
    add_action('wp_admin_enqueue_scripts',[$this,'add_assets']);
    //
  }


  public function add_assets() {
    //
    foreach ($this->style_handles as $style_handle) {
      wp_register_style(
        $style_handle,
        plugin_dir_url(__FILE__) .
        '../style/' . 'set_admin_' . $style_handle . '_style.css'
      );
    }
    //
    foreach ($this->script_handles as $script_handle) {
      wp_register_script(
        $script_handle,
        plugin_dir_url(__FILE__) .
        '../lib/' . 'set_admin_' . $script_handle . '_script.js',
        array(),
        null,
        true
      );
    }
    //
  }


  public function set_register_options_pages () {
    //
    add_menu_page(
      'Set Options', // Page Title
      'Set',  // Menu Title
      'manage_options', //capability
      'set',  //menu_slug
      [$this,'set_options_page'],//cb function
      'dashicons-flag',
      22
    );
    //
    add_submenu_page(
      'set', // parent slug
      'Set Sub-Options', // Page Title
      'Set Sub-Options',  // Menu Title
      'manage_options', //capability
      'set_suboptions',  //menu_slug
      [$this,'set_suboptions_page']//cb function
    );
  }


  public function set_init_settings_api() {
    //
    add_settings_section(
      'set',         //unique id
      'Set Options Section',         //title
      [$this,'set_options_section'],    //call back function
      'set'        //page_slug
    );
    //
    add_settings_field(
      'api_key', //id
      'Set API Key', //label
      [$this,'set_api_key_field'],    //call back function
      'set',    // page slug
      'set'     //section (parent settings-section uniqueID)
    );
    //
    add_settings_field(
      'publish', //id
      'Set Publish Now?', //label
      [$this,'set_publish_field'],    //call back function
      'set',    // page slug
      'set'     //section (parent settings-section uniqueID)
    );
    //
    add_settings_section(
      'set_suboptions',         //unique id
      'Set Suboptions Section',         //title
      [$this,'set_suboptions_section'],    //call back function
      'set_suboptions'        //page_slug
    );
    //
    add_settings_field(
      '1', //id
      'Set Suboption 1', //label
      [$this,'set_suboption_1_field'],    //call back function
      'set_suboptions',    // page slug
      'set_suboptions'     //section (parent settings-section uniqueID)
    );

    register_setting(
      'set',
      'set'
    );

    register_setting(
      'set_suboptions',
      'set_suboptions'
    );
  }


  protected function collect_section_overhead($prop_slug,$db_slug,$path_slug) {
    //
    $db_slug = ($db_slug) ? '_' . $db_slug : '';
    //
    $this->{$prop_slug} =
      !empty( get_option('set' . $db_slug) ) ?
        get_option('set' . $db_slug) : [];
    //
    if (in_array($path_slug,$this->style_handles)) {
      wp_enqueue_style($path_slug);
    }
    //
    if (in_array($path_slug,$this->script_handles)) {
      wp_enqueue_script($path_slug);
    }
  }


  public function set_options_section() {
    //
    $this->collect_section_overhead('options','','main');
    //error_log('settings overhead call');
    //error_log(print_r($this->options,true));
    //
    ?>
    <div class="set-signal">
      This is the Options Section of the Options Page
    </div>
    <?php
    //
  }


  public function set_api_key_field() {
    //
    $val = !empty($this->options['api_key']) ? $this->options['api_key'] : '';
    $att = ($val) ? 'value' : 'placeholder';
    $val = ($val) ? $val : 'not set';
    //
    ?>
    <label for="set_api_key">API Key:</label>
    <input type="text" id="api-key" class="set-admin"
     name="set[api_key]" <?php echo $att ."='". $val  ."'"?> />
    <?php
    //
  }


  public function set_publish_field() {
    //
    $val = (!empty($this->options['publish']) && $this->options['publish']) ?
      $this->options['publish'] : '';

    if ($val) {
      //error_log('publish value is set');
      if (!class_exists('Set_Publisher')) {
        include_once 'set_publisher.php';
      }
      //
      $publisher = new Set_Publisher('set');
      //
      if (!$publisher->error) {
        //error_log('valid data for publication');
        $publisher->publish('set','templates/template-full-width.php');

        $opts = get_option('set');
        $opts['publish'] = 0;
        update_option('set',$opts);
      }
      //
      if ($publisher->error) {
        error_log(print_r($publisher->error,true));
      }
    }
    //error_log('the latest options fetch--should include both api and publish props');
    //error_log(print_r($this->options,true));
    ?>
    <label for="set_publish_checkbox">Publish Now?</label>
    <input type="checkbox" id="publish" class="set-checkbox"
      name="set[publish]" value="1" />
    <?php
    //
  }


  public function set_suboptions_section () {
    //
    $this->collect_section_overhead('suboptions','suboptions','main');
    //
    ?>
    <div class="set-signal">
      This is the Sub-Options Section of the Sub-Options Page
    </div>
    <?php
    //
  }


  public function set_suboption_1_field() {
    //
    $val = !empty($this->suboptions[1]) ? $this->suboptions[1] : '';
    $att = ($val) ? 'value' : 'placeholder';
    $val = ($val) ? $val : 'not set';
    //
    ?>
    <label for="set_suboption_1"></label>
    <input type="text" id="suboption-1" class="set-admin"
     name="set_suboptions[1]" <?php echo $att ."='". $val  ."'"?> />
    <?php
    //
  }


  public function set_options_page () {
    //
    $this->set_options_form('set');
  }


  public function set_suboptions_page () {
    //
    $this->set_options_form('set_suboptions');
  }


  protected function set_options_form ($prop) {
    //
    echo "<form method='POST' action='options.php' id='$prop'>";
    //
    settings_fields( $prop );
    do_settings_sections( $prop );
    submit_button();
    //
    echo '</form>';
  }

}

?>
