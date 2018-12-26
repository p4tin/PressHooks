<?php
/*
  Plugin Name: PressHooks - Exposing Wordpress to the outside world!
  Plugin URI: http://localhost/x
  Description: Creates webhooks for most wordpress internal hooks
  Version: 0.1
  Author: Paul Fortin
  Author URI: http://twitter.com/hiddenpearls
  License: MIT license
  Text Domain: wp-hooks
*/

defined( 'ABSPATH') or die;

include_once("settings.php");

class WP_PressHooks{

    /**
     * Holds the settings values
     */
    private $options;

  // Constructor
    function __construct() {
        add_action( 'post_updated', array($this, 'wpa_log_post_edit'), 1, 3);
        add_action( 'wp_login', array($this, 'wpa_log_user_login'), 1, 2);
        add_action( 'wp', array($this, 'wpa_log_page_loading') );

        $this->options = get_option( 'presshooks_name' );
    }

    public function post_data_to_url($hook_data){
        //$url = "http://10.0.0.9:9200/events/wordpress";
        //$url = "http://requestbin.fullcontact.com/x9krugx9";
        if (isset( $this->options['target_id'])) {
            $url = $this->options['target_id'];
            $options = array(
                'http' => array(
                    'method'  => 'POST',
                    'content' => json_encode( $hook_data ),
                    'timeout' => 10,
                    'header'=>  "Content-Type: application/json\r\n"
                )
            );

            $context  = stream_context_create( $options );
            $content = @file_get_contents( $url, false, $context );
            if($content === FALSE) {
                error_log("Error from  @file_get_contents");
            }
        } else {
            error_log("URL option not set, cannot post events.");
        }
    }

    public function wpa_log_post_edit( $post_ID, $post_after, $post_before ) {
        $event = array();
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $event["@timestamp"] =  $now->format("m-d-Y H:i:s.u");
        $event["event_name"] = "post_updated";
        $event["post_id"] = $post_ID;
        $event["post"] = $post_after;
        $event["diffs"] = array_diff_assoc((array)$post_before, (array)$post_after);

        $this->post_data_to_url($event);
        error_log(json_encode($event));
    }

    public function wpa_log_user_login( $user_login, $user ) {
        $event = array();
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $event["@timestamp"] =  $now->format("m-d-Y H:i:s.u");
        $event["event_name"] = "wp_login";
        $event["user"] = $user;

        $this->post_data_to_url($event);
        error_log(json_encode($event));
    }

    public function wpa_log_page_loading() {
        $event = array();
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $event["@timestamp"] =  $now->format("m-d-Y H:i:s.u");
        $event["event_name"] = "navigation";

        global $wp;
        $current_url = home_url(add_query_arg(array(),$wp->request));
        $user = wp_get_current_user();

        $event["page"] = $current_url;
        $event["user"] = $user->user_login. "[" . $user->user_email . "]";

        $this->post_data_to_url($event);

        error_log(json_encode($event));
    }
}

if (class_exists( 'WP_PressHooks' )){
    $pressHooks = new WP_PressHooks();
}
