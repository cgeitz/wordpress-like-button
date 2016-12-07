<?php
/*
Plugin Name: Like Button
Plugin URI: https://behindthechair.com
Description: Adds Like Button
Version: 0.1.0
Author: Doejo
Author URI: http://doejo.com
*/

 if( !defined('ABSPATH') ){
	exit;
}

function my_enqueue() {

    wp_enqueue_script( 'scripts', plugin_dir_url( __FILE__ ) . 'includes/scripts.js', array('jquery'), '1.0.0', false );

    $is_logged_in = is_logged_in();


    wp_localize_script( 'scripts', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'is_logged_in' => $is_logged_in ) );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue' );


function is_logged_in() {
  $user = wp_get_current_user();
  if ($user->user_email) {
    return 'true';
  }
  else {
    return 'false';
  }

}

add_action('wp_enqueue_scripts', 'is_logged_in' );


add_action( 'wp_enqueue_scripts', 'likes_enqueue_styles' );
function likes_enqueue_styles() {
	wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . 'includes/style.css', array(), '', false);
}


function btc_like_button( $atts ){
	$id = $atts['post_id'];
	$id = intval($id);
	$user = wp_get_current_user();
	$like_count = get_post_meta($id, 'like_count', true);

	echo '<div class="btc-likes" data-post-id="' . $id . '">';

	if($like_count){
		$liked_string = get_user_meta($user->ID, '_liked_array', true);
		$liked_string = unserialize($liked_string);


		if( in_array($id,$liked_string) ){
			echo '<span class="btc-heart btc-liked"></span>';
		}else{
			echo '<span class="btc-heart btc-not-liked"></span>';
		}

		echo '<span class="like-count" data-likes="' . $like_count . '""> ';
			echo $like_count;
			echo ' likes';
		echo '</span>';

		echo '<div class="data-holder"></div>';

	}else{
		echo '<span class="btc-heart btc-not-liked"></span>';
		echo '<span class="like-count" data-likes="' . $like_count . '""> ';
		echo 'Be the first to like this!</span>';
    	echo '<div class="data-holder"></div>';
	}
	echo '</div>';
}
add_shortcode( 'like_button', 'btc_like_button' );


function btc_like_post(){
  $user = wp_get_current_user();


	$post_id = $_POST['id'];


	$liked_string = get_user_meta($user->ID, '_liked_array', true);

	if($liked_string){
		$liked_array = unserialize($liked_string);
		//var_dump($liked_array);

		if(  in_array($post_id, $liked_array, true) ){
			exit;
		}else{
			array_push($liked_array, $post_id);

			$liked_string = serialize($liked_array);


			$result = update_user_meta( $user->ID, '_liked_array', $liked_string );
		}

	}else{
		$liked_array = array($post_id);

		$liked_string = serialize($liked_array);

		add_user_meta( $user->ID, '_liked_array', $liked_string, true );
	}
	exit;
}

add_action('wp_ajax_btc_like_post', 'btc_like_post');
add_action('wp_ajax_nopriv_btc_like_post', 'btc_like_post');

function btc_unlike_post(){
	$user = wp_get_current_user();


	$post_id = $_POST['id'];


	$liked_string = get_user_meta($user->ID, '_liked_array', true);

	if($liked_string){
		$liked_array = unserialize($liked_string);

		if(  in_array($post_id, $liked_array, true) ){
			$liked_array = array_diff($liked_array, array($post_id));

			$liked_string = serialize($liked_array);

			$result = update_user_meta( $user->ID, '_liked_array', $liked_string );
		}

	}

  $like_count = get_post_meta($post_id, 'like_count', true);

  if($like_count == '1'){
    update_post_meta($post_id, 'like_count', '0');
  }

	exit;
}

add_action('wp_ajax_btc_unlike_post', 'btc_unlike_post');
add_action('wp_ajax_nopriv_btc_unlike_post', 'btc_unlike_post');


function btc_update_like_count(){
	$user_query = new WP_User_Query( array( 'meta_key' => '_liked_array') );
  // var_dump($user_query->results['0']->data->ID);

  // $user_id = $user_query->results['0']->data->ID;


	$post_ids = array();

	// User Loop
	if ( ! empty( $user_query->results ) ) {
		foreach ( $user_query->results as $user ) {
			 $user_liked_posts = get_user_meta($user->ID, '_liked_array', true);
			 $user_liked_posts = unserialize($user_liked_posts);

      //  var_dump($user_liked_posts);

			 /*
			  * This takes the array of posts that each user has liked
			  * and counts the occurences of each post ID then stores in an array
			  */
			foreach ($user_liked_posts as $key => $single_post_id) {
			 	if( isset( $post_ids["{$single_post_id}"] ) ){
			 		$post_ids["{$single_post_id}"]++;
			 	}else{
			 		$post_ids["{$single_post_id}"] = 1;
			 	}
			}
		}

    // var_dump($post_ids);

	} else {
		return;
	}

  // var_dump($post_ids);

	foreach ($post_ids as $post_id => $count) {
		$like_count = get_post_meta($post_id, 'like_count', true);

		if($like_count != null){
			update_post_meta($post_id, 'like_count', $count);
		}else{
			add_post_meta($post_id, 'like_count', $count, true);
		}
	}


	 wp_reset_postdata();

}

function my_cron_schedules($schedules){
    if(!isset($schedules["1min"])){
        $schedules["1min"] = array(
            'interval' => 1*60,
            'display' => __('Once every 1 minute'));
    }
    if(!isset($schedules["30min"])){
        $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');
//////////////

register_activation_hook(__FILE__, 'my_activation');

function my_activation() {
    if (! wp_next_scheduled ( 'my_hourly_event' )) {
	wp_schedule_event(time(), '30min', 'my_hourly_event');
    }
}

add_action('my_hourly_event', 'do_this_hourly');

function do_this_hourly() {
	btc_update_like_count();
}






?>
