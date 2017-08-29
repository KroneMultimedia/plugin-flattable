<?php

namespace KMM\Flattable;

use DateTime;
use DateTimeZone;

class Core
{
    private $plugin_dir;
    public function __construct()
    {
        $this->plugin_dir = plugin_dir_url(__FILE__) . '../';
        $this->add_filters();
    }

    private function add_filters()
    {
        add_action('save_post', [$this, 'save_post'], 10, 3);

        add_action('krn_flattable_check_table', [$this, 'checkTable'], 10, 2);

        //DEMO
        add_filter('krn_flattable_enabled_article', [$this, "flattable_enabled"], 10, 2);
        add_filter('krn_flattable_columns_article', [$this, "flattable_columns"], 10, 2);
        add_filter('krn_flattable_values_article', [$this, "flattable_values"], 10, 2);
        add_filter('krn_flattable_pre_write_article', [$this, 'flattable_pre_write'], 10, 2);

        /*
         * DEMO QUERY
         * 
         select 
          	article.*
         from
             wp_flattable_articles_in_ressort pir
            LEFT JOIN wp_flattable_article article ON article.post_id = pir.post_id
          where
	          pir.post_ressort in(28);


        INDEX: articles_in_ressort -> post_ressort,
               articles flat -> post_id
	
         */
    }
    public function flattable_enabled($state, $postObject = null)
    {
        return true;
    }
    public function flattable_pre_write($columns, $postObject) {
      global $wpdb;
      $new_columns = [
          ["column" => "post_id", "type" =>  "int(12)", "printf" => "%d"],
          ["column" => "post_ressort", "type" =>  "int(12)", "printf" => "%d"],
      ];
      //Check that the posts<->ressort table exists
      do_action('krn_flattable_check_table', 'articles_in_ressort', $new_columns);


      $table_name = $wpdb->prefix . 'flattable_articles_in_ressort';

      //Delete all current relations:
      $wpdb->query("delete from " . $table_name .  " where post_id = " . $postObject->ID);


      //Insert new Relations
      $ressort_repater = get_field('field_58512668ff1d2', $postObject->ID);
      foreach($ressort_repater as $rep) {
        $sql = "insert into $table_name (post_id, post_ressort) values(" . $postObject->ID . ", " . $rep["ressort_id"]->ID . ")";
        $wpdb->query($sql);
      }

    }
    public function flattable_values($data, $postObject)
    {
        return [
            "post_title" => $postObject->post_title,
            "post_status" => $postObject->post_status
      ];
    }
    public function flattable_columns($columns, $postObject)
    {
        return [
          ["column" => "post_title", "type" =>  "varchar(100)", "printf" => "%s"],
          ["column" => "post_status", "type" =>  "varchar(100)", "printf" => "%s"],
      ];
    }
    public function save_post($postId, $postObject, $update)
    {
        global $wpdb;
        if(!isset($_POST["post_type"])) return;
        $postType = $_POST["post_type"];
        $table_name = $wpdb->prefix . 'flattable_' .  $postType;
        //check if flattable is enabled for this post type.
        $enabled = apply_filters('krn_flattable_enabled_' . $postType, false, $postObject, $postObject);
        if ($enabled) {
           //We are in flattable enabled mode.
          //get a list of columns.
          $defaultCols = [
            ["column" => "post_id", "type" =>  "int(12)"],
            ["column" => "post_type", "type" =>  "varchar(100)"],
          ];
          $columns = apply_filters('krn_flattable_columns_' . $postType, $defaultCols, $postObject);
          do_action('krn_flattable_pre_write_' . $postType, $columns, $postObject);
          //check if table exists, and if table has atleast required columns
          if ($this->checkTable($postType, $columns)) {
              $db_cols = [];
              $assoc_db = [];
              foreach ($columns as $column) {
                  $db_cols[] = $column["column"];
                  $assoc_db[$column["column"]] = $column;
              }

              $finalFields = apply_filters('krn_flattable_values_' . $postType, [], $postObject);
              if($update) {
                //Check if flat table record exists
                // if not existig switch to INSERT
                //FIXME
                $checkRow = $wpdb->get_row("select post_id from $table_name where post_id=" . $postId);
                if(!$checkRow) {
                  $update = false;
                }
              }
              if (!$update) {
                  //INSERT
                  $updateCols = ["post_type", "post_id"];
                  $updateVals = ["'" . $postType . "'", $postId];
                  $updateInserValues = [];  
                  foreach ($finalFields as $key => $value) {
                      $updateCols[] =  $key;
                      $updateVals[] = $assoc_db[$key]['printf'];
                      $updateInserValues[] = $value;
                  }
                  $sql = " insert into $table_name (" .  join(",", $updateCols) . ") VALUES(" . join(",", $updateVals) . ")";
                  $query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $updateInserValues));
                  $wpdb->query($query);

              } else {
                  //UPDATE

                  //$v = get_field('field_58512668ff1d2', $postId);
                  //echo "<pre>";
                  //var_dump($v);
                  //exit;
                  $updateCols = [];
                  $updateVals = [];
                  foreach ($finalFields as $key => $value) {
                      $updateCols[] =  $key . " = " . $assoc_db[$key]["printf"];
                      $updateVals[] = $value;
                  }
                  $updateVals[] = $postId;

                  $sql = "update $table_name SET " . join(",", $updateCols) . " WHERE post_id = %d";
                  $query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $updateVals));
                  $wpdb->query($query);
              }

              do_action('krn_flattable_post_write_' . $postType, $postObject);
          }
        }
    }
    public function checkTable($postType, $columns)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'flattable_' .  $postType;
    
        $charset_collate = $wpdb->get_charset_collate();

        $sql_columns = [];
        foreach ($columns as $column) {
            $sql_columns[] = $column["column"] . " " . $column["type"];
        }

        $column_string = join(",", $sql_columns);

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		              id int(12) NOT NULL AUTO_INCREMENT,
                  $column_string
		              ,PRIMARY KEY  (id)
	        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $a = dbDelta($sql);

        //Check columns
        foreach ($columns as $column) {
            $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = '$table_name' AND column_name = '" . $column['column'] . "'");

            if (empty($row)) {
                $wpdb->query("ALTER TABLE $table_name ADD " . $column['column'] . " " . $column['type']);
            }
        }

        return true;
    }
}
