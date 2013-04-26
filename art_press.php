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
                            <label for='attachments[{$post->ID}][art_type]'>Art Category:</label>
                                <select name='attachments[{$post->ID}][art_type]' id='attachments[{$post->ID}][art_type]'>
                                  <option value='paint'".($current_type == 'paint'?' selected':'').">Paint</option>
                                  <option value='sculpture'".($current_type == 'sculpture'?' selected':'').">Sculpture</option>
                                  <option value='mixed'".($current_type == 'mixed'?' selected':'').">Mixed Media</option>
                                  <option value='photo'".($current_type == 'photo'?' selected':'').">Photography</option>
                                </select><br />
                                <label for='art_price'>Art Price:</label><input name='attachments[{$post->ID}][art_price]' type='text' id='art_price' value='".($price?$price:"0")."' />
                            </div>",
		'helps' => 'Specify the art medium or type.',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'attachment_art_info', 10, 2 );

function attachment_art_info_save( $post, $attachment ) {
    if($attachment['is_art']){
        $post_ob = get_post($post->ID);
        // check to see if there is a button already
        $buttonID = get_post_meta($post->ID, 'PPButtonID', true);
        
        $paypalService = new PayPalAPIInterfaceServiceService();
        // There is a button, get the button from DB
        if(count($buttonID) != 0)
        {
            $modifyDate = get_post_meta($post->ID, 'PPButtonModDate', true);
            // step 1, make request type
            $requestType = new BMButtonSearchRequestType();
            $requestType->StartDate = $modifyDate;
            // step 2, make request and set type
            $buttonSearchReq = new BMButtonSearchReq();
            $buttonSearchReq->BMButtonSearchRequest = $requestType;
            // step 3, send request
            try {
                $buttonSearchResponse = $paypalService->BMButtonSearch($buttonSearchReq);
                error_log(print_r($buttonSearchResponse, true));
            } catch (Exception $ex) {
                require '../Error.php';
            }
            // step 4, deal with the data
            if($buttonSearchResponse && $buttonSearchResponse->ButtonSearchResult)
                for($i = 0;$i<count($buttonSearchResponse->ButtonSearchResult);$i++)
                {
                    
                }
            // update old button
            if($buttonID){}
            // create new button
            else{}
            // set new button
        }
        else
        {
            
        }
        update_post_meta( $post['ID'], 'art_type', $attachment['art_type'] );
        update_post_meta( $post['ID'], 'art_price', $attachment['art_price'] );
    }
    update_post_meta($post['ID'], 'is_art', $attachment['is_art']);
    return $post;
}

add_filter( 'attachment_fields_to_save', 'attachment_art_info_save', 10, 2 );
?>