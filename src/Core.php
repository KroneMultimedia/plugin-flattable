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
        add_action('save_post', [$this, 'save_post'], 100, 3);

        add_action('krn_flattable_check_table', [$this, 'checkTable'], 10, 2);
        add_action('krn_flattable_publish', [$this, 'manualPublish'], 10);

        add_action('init', [$this, 'init'], 30);

        //DEMO

    }
    public function init() {
       $post_types = get_post_types();
       foreach($post_types as $type) {
         add_action('rest_insert_' . $type, array($this, "rest_update"), 10, 3);
       }
    }
    public function rest_update($postObj, $request, $update) {
        /*
        * class-wp-rest-attachments-controller.php calls the action with $attachment as array, and also calls parent::update_item(),
        *  -> parent class is class-wp-rest-posts-controller.php, that also calls the action, but with $attachment as type WP_Post
        */
        if ( ! $postObj instanceof \WP_Post ) {
            return;
        }
        $_POST["post_type"] = $postObj->post_type;
        // save all the data in an anonymous function
        $trigger_func = function ( $response, $handler, $request ) use ( $postObj, $update ) {
            // call the internal save_post after all postmeta is written
            $this->save_post($postObj->ID, $postObj, $update);
            return $response;
        };
        // add a filter => after all callbacks are called (after update_additional_fields_for_object())
        add_filter( 'rest_request_after_callbacks', $trigger_func, 10, 3 );
    }
    public function manualPublish($postId) {
      $postObj = get_post($postId);
      $_POST["post_type"] = $postObj->post_type;
      $this->save_post($postId, $postObj, true);
    }
    public function save_post($postId, $postObject, $update)
    {
        global $wpdb;
        if(!$postObject) {
          $postType = $_POST["post_type"];
        } else {
          $postType = $postObject->post_type;
        }
        if(!$postType) { 
            return;
        }

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
          $columns = apply_filters('krn_flattable_columns_' . $postType, [], $postObject);
          $columns = array_merge($defaultCols, $columns);
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

              $checkRow = $wpdb->get_row("select post_id from $table_name where post_id=" . $postId);
              //if we have already a published record, update it
              $update = true;
              if(!$checkRow) {
                  $update = false;
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
