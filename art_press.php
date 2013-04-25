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
        $url = get_bloginfo('template_directory') . '/js/art-info.js';
        echo '"<script type="text/javascript" src="'. $url .'"></script>"';
    }
}

add_filter('admin_head', 'add_jquery_data');
 
function attachment_art_info( $form_fields, $post ) {
    $current_type = get_post_meta($post->ID, 'art_type', TRUE);
    
	$form_fields['art_type'] = array(
		'label' => 'Art Information',
		'input' => 'html',
		'html' => "<input name='attachments[{$post->ID}][is_art]' type='checkbox' id='is_art' />Is art?
                            <div id='art_info' style='display: none;'>
                                <select name='attachments[{$post->ID}][art_type]' id='attachments[{$post->ID}][art_type]'>
                                  <option value='paint'".($current_type == 'paint'?' selected':'').">Paint</option>
                                  <option value='sculpture'".($current_type == 'sculpture'?' selected':'').">Sculpture</option>
                                  <option value='mixed'".($current_type == 'mixed'?' selected':'').">Mixed Media</option>
                                  <option value='photo'".($current_type == 'photo'?' selected':'').">Photography</option>
                                </select>
                                <input name='attachments[{$post->ID}][art_price]' type='text' id='art_price' />
                            </div>",
		'helps' => 'Specify the art medium or type.',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'attachment_art_info', 10, 2 );

function attachment_art_info_save( $post, $attachment ) {
    $post_ob = get_post($post->ID);
    error_log(print_r($post, true));
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
        
        
        update_post_meta( $post['ID'], 'art_type', $attachment['art_type'] );
	return $post;
}

add_filter( 'attachment_fields_to_save', 'attachment_art_info_save', 10, 2 );
?>