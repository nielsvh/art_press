<?php
/*
Plugin Name: Art Press Plugin
Description: This plugin allows you to enter the art catogory, price, and paypal
 info for sales of art.
Version: 1.0
Author: Niels van Hecke
*/

/* Include paypal button manager */
require('PPBootStrap.php');

/*include krumo function (replaces var_log and print_r with human readable output) */
require('krumo_0-2-a/class.krumo.php');

function add_jquery_data() {
    global $parent_file;
    
    if ( isset( $_GET['action'] ) && strcmp($_GET['action'], 'edit') == 0 && isset( $_GET['post'] ) && strcmp($parent_file, 'upload.php') == 0){
        wp_enqueue_script(
		'custom-script',
		plugins_url().'/art_press/art-info.js',
		array( 'jquery' )
	);
    }
}

add_filter('admin_head', 'add_jquery_data');
 
function attachment_art_info( $form_fields, $post ) {
    $is_art = get_post_meta($post->ID, 'is_art', TRUE);
    $current_type = get_post_meta($post->ID, 'art_type', TRUE);
    $price = get_post_meta($post->ID, 'art_price', TRUE);
    
	$form_fields['art_type'] = array(
		'label' => 'Art Information',
		'input' => 'html',
		'html' => "<label for='is_art'>Art item for sale? </label><input name='attachments[{$post->ID}][is_art]' type='checkbox' id='is_art' ".($is_art?"checked='true'":"")."/>
                            <div id='art_info' style='display: none;'>
                                <select name='attachments[{$post->ID}][art_type]' id='attachments[{$post->ID}][art_type]'>
                                  <option value='paint'".($current_type == 'paint'?' selected':'').">Paint</option>
                                  <option value='sculpture'".($current_type == 'sculpture'?' selected':'').">Sculpture</option>
                                  <option value='mixed'".($current_type == 'mixed'?' selected':'').">Mixed Media</option>
                                  <option value='photo'".($current_type == 'photo'?' selected':'').">Photography</option>
                                </select>
                                <input name='attachments[{$post->ID}][art_price]' type='text' id='art_price' value='".($price?$price:"0")."' />
                            </div>",
		'helps' => 'Specify the art medium or type.',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'attachment_art_info', 10, 2 );

function attachment_art_info_save( $post, $attachment ) {
    $post_ob = get_post($post->ID);
    // check to see if there is a button already
    $button = get_post_meta($post->ID, 'PPButton');

    $buttonSearchRequest = new BMButtonSearchRequestType();
    $buttonSearchRequest->StartDate = $post_ob->post_date;

    $buttonSearchReq = new BMButtonSearchReq();
    $buttonSearchReq->BMButtonSearchRequest = $buttonSearchRequest;

    $paypalService = new PayPalAPIInterfaceServiceService();
    try {
        $buttonSearchResponse = $paypalService->BMButtonSearch($buttonSearchReq);
        error_log(print_r($buttonSearchResponse, true));
    } catch (Exception $ex) {
        require '../Error.php';
    }
    // update old button
    if($button){}
    // create new button
    else{}
    // set new button

    
    update_post_meta($post['ID'], 'is_art', $attachment['is_art']);
    update_post_meta( $post['ID'], 'art_type', $attachment['art_type'] );
    update_post_meta( $post['ID'], 'art_price', $attachment['art_price'] );
    return $post;
}

add_filter( 'attachment_fields_to_save', 'attachment_art_info_save', 10, 2 );
?>