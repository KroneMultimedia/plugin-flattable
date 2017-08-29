<?php

namespace KMM\Article;

use DateTime;
use DateTimeZone;

class Core
{
    private $plugin_dir;
    public function __construct($i18n)
    {
        $this->plugin_dir = plugin_dir_url(__FILE__) . '../';
        $this->add_filters();
    }

    private function add_filters()
    {
        add_action('save_post', [$this, 'save_post']);
        var_dump(1);
        exit;
    }
    public function save_post($postId) {
      global $post_data;
      var_dump($post_data);
      exit;
    }
}
