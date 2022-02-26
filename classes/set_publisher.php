<?php

class Set_Publisher {

  protected $path;
  protected $indexed_posts;
  protected $nested_post_data;
  protected $post_titles_by_id;
  protected $post_ids_by_slug;
  protected $tier_val_by_slug;

  public $error;

  public function __construct($filename) {
    $this->tier_val_by_slug = [];
    $this->error = null;

    $this->path = "/../records/{$filename}.csv";
    $this->indexed_posts = $this->import_csv_index($this->path);

    if ($this->indexed_posts) {

      $this->nested_post_data = $this->nest_post_data($this->indexed_posts);

      // collect data struct errors
      if (!count($this->nested_post_data)) {
        $this->error = ['code'=>2];
      }
    } else {
      $this_error = ['code'=>2];
    }
  }

  protected function import_csv_index($relpath) {
    // 2d array : bool
    $result = array();
    if (($handle = fopen(__DIR__  . $relpath, "r")) !== FALSE) {
      //
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $result[] = $data;
      }
      //
      fclose($handle);
      return $result;
    } else {
      error_log("could not open file at $relpath");
      return false;
    }
  }

  protected function nest_post_data($table) {
    // 3d array
    $col_names = ['slug','title','css_dep','js_deps','parent_slug'];
    $nested_post_data = [];
    $tier_index = 0;
    //
    foreach($table as $table_row) {
      //
      $new_row = [];
      $col_index = 0;
      // give each table row key=>val pair data struct
      foreach($col_names as $col_name) {

        //
        $new_row[$col_name] = $table_row[$col_index];
        //
        $col_index++;
      }
      //error_log('iterating next data row:');
      //error_log(print_r($new_row,true));
      if ($new_row['parent_slug']) {
        //
        //error_log('child  resources');
        //error_log(print_r($this->tier_val_by_slug,true));

        $tier_index = $this->tier_val_by_slug[$new_row['parent_slug']]+1;
        $this->tier_val_by_slug[$new_row['slug']] = $tier_index;
      } else {
        //
        //error_log('tier1 resources');
        //error_log(print_r($this->tier_val_by_slug,true));

        $this->tier_val_by_slug[$new_row['slug']] = 0;
      }

      if (empty($nested_post_data[$tier_index])) {
        $nested_post_data[$tier_index] = [];
      }
      //
      $nested_post_data[$tier_index][] = $new_row;
    }
    return $nested_post_data;
  }

  protected function inject_shortcode_atts($tag_base, $style_slugs, $script_slugs) {
    // string
    $result = "[{$tag_base}_template ";
    $result .= ($style_slugs) ?  "style_slugs='{$style_slugs}' " : '';
    $result .= ($script_slugs) ? "script_slugs='{$script_slugs}' " : '';
    $result .= ' ]';
      //
    return $result;
  }


  protected function write_nested_posts($rows_arr,$domain,$template_path) {
    // void - database ops
    global $wpdb;
    $table_name = $wpdb->prefix . 'posts';
    //error_log($table_name);
    $test_query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

    if ( $wpdb->get_var( $test_query ) == $table_name ) {
     //
      $row_key = 0;
      foreach($rows_arr as $rows_tier) {

        foreach($rows_tier as $post_row) {
           //
          $mypost = array(
            'post_title' => $post_row['slug'],
            'post_content' => $this->inject_shortcode_atts(
              $domain,
              $post_row['css_dep'],
              $post_row['js_deps']
            ),
            'post_type'=> 'page',
            'post_status'=> 'publish',
            'post_date_gmt'=> date( 'Y-m-d H:i:s', time() ),
            'page_template' => $template_path
          );
          //
          if ($row_key) {
             $parent_ID = $this->post_ids_by_slug[ $post_row['parent_slug'] ];
             $mypost['post_parent'] = $parent_ID;
          }
          //
          $ID = wp_insert_post($mypost);
          //
          if ($ID) {
            $this->post_ids_by_slug[$post_row['slug']] = $ID;
            $this->post_titles_by_id[$ID] = $post_row['title'];
            //
          } else {
            if ( !empty($this->error['post_errors']) ) {
              $this->error['post_errors'][] = $post_row['slug'];
            } else {
              $this->error['post_errors'] = [$post_row['slug']];
              $this->error['code'] = 3;
            }
          }
        }
        $row_key++;
      }
    }
  }

  protected function update_post_titles() {
    // void - databse ops
    global $wpdb;
    $table_name = $wpdb->prefix . 'posts';
     //error_log($table_name);
    $test_query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

    if ( $wpdb->get_var( $test_query ) == $table_name ) {

      foreach($this->post_ids_by_slug as $slug => $id) {

        $newtitle = $wpdb->update(
        $table_name,
          array( 'post_title' => $this->post_titles_by_id[$id]),
          array( 'ID' => $id ),
          array( '%s' )
        );
        if (!$newtitle) {
          if ( !empty($this->error['retitle_errors']) ) {
            $this->error['retitle_errors'][] = $post_row['slug'];
          } else {
            $this->error['retitle_errors'] = [$post_row['slug']];
            $this->error['code'] = 4;
          }
        }
      }
    }
  }

  public function publish($app_domain,$template_file_path) {
   // void - init methods
   $this->write_nested_posts(
     $this->nested_post_data,
     $app_domain,
     $template_file_path
   );

   $this->update_post_titles();

  }

}
?>
