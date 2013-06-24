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

/* Add attachment art script to photo upload and edit pages. */
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

/* Add art info fields for attachments that are for sale. */
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
                                <label for='art_price'>Art Price:</label><input name='attachments[{$post->ID}][art_price]' type='text' id='art_price' value='".($price?$price:"0")."' /><br />
                                <lable for='button_id'>PayPal Button ID: </lable><input id='button_id' type='text' value='".get_post_meta($post->ID, 'PPButtonID', TRUE)."' disabled='disabled' />
                            </div>",
		'helps' => 'Specify the art medium or type.',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'attachment_art_info', 10, 2 );

/* Save art info meta data and setup button information. */
function attachment_art_info_save( $post, $attachment ) {
    if($attachment['is_art']){
        $paypalService = new PayPalAPIInterfaceServiceService();
        $buttonID = get_post_meta($post['ID'], 'PPButtonID', TRUE);
        $button = "";
        $button = get_button($paypalService, $buttonID);
        if($buttonID && $button->Ack=='Success'){
            $button = update_button($paypalService, $button, $post['post_title'], $attachment['art_price']);
        }
        else
        {
            $button = make_button($paypalService, $post['post_title'], $attachment['art_price']);
        }
        update_post_meta($post['ID'], 'IsTestButton', TRUE);
        if($button->Ack != 'Failure'){
            update_post_meta($post['ID'], 'PPButtonID', $button->HostedButtonID);
        }
        else{
            echo "Error creating/updating paypal button. Check logs for more details.";
            error_log($button, 3, "/home/charl144/public_html/error_log");
        }
        update_post_meta( $post['ID'], 'art_type', $attachment['art_type'] );
        update_post_meta( $post['ID'], 'art_price', $attachment['art_price'] );
    }
    update_post_meta($post['ID'], 'is_art', $attachment['is_art']);
    return $post;
}

add_filter( 'attachment_fields_to_save', 'attachment_art_info_save', 10, 2 );

// get button
function get_button($paypalService, $buttonID){
    $requestType = new BMGetButtonDetailsRequestType();
    $requestType->HostedButtonID = $buttonID;
    $buttonDetailsReq = new BMGetButtonDetailsReq();
    $buttonDetailsReq->BMGetButtonDetailsRequest = $requestType;
    try {
        return $paypalService->BMGetButtonDetails($buttonDetailsReq);
    } catch (Exception $ex) {
        require '../Error.php';
    }
}

// set button
function make_button($paypalService, $itemName, $price)
{
    $requestType = new BMCreateButtonRequestType();
    $requestType->ButtonType = 'CART';
    $requestType->ButtonCode = 'HOSTED';
    $requestType->ButtonVar = Array("item_name=" . $itemName,
                                        "add=\"1\"",
                                        "shopping_url=" . ART_SITE,
                                        "return=" . ART_SITE,
					"business=" . TEST_EMAIL,
					"amount=" . $price);
    $request = new BMCreateButtonReq();
    $request->BMCreateButtonRequest = $requestType;
    
    try {
        $buttonResponse = $paypalService->BMCreateButton($request);
        if($buttonResponse->Ack == "Success")
        {
            return $buttonResponse;
        }
    } catch (Exception $ex) {
        require '../Error.php';
    }
    return "";
}

// update button
function update_button($paypalService, $button, $itemName, $price)
    {
        $requestType = new BMUpdateButtonRequestType();
        $requestType->HostedButtonID = $button->HostedButtonID;
        $requestType->ButtonType = ($button->ButtonType == 'CART'?'CART':'ADDCART');
        $requestType->ButtonCode = $button->ButtonCode;
        $requestType->ButtonVar = Array("item_name=" . $itemName,
					"amount=" . $price);
        
        $request = new BMUpdateButtonReq();
        $request->BMUpdateButtonRequest = $requestType;
        try {
            return $paypalService->BMUpdateButton($request);
        } catch (Exception $ex) {
            require '../Error.php';
        }
    }
?>