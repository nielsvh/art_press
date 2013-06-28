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
		plugins_url()."/art_press/art-info.js",
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
    $prices = get_post_meta($post->ID, 'photo_prices', TRUE);
    $sizes = get_post_meta($post->ID, 'photo_sizes', TRUE);
    //error_log(print_r($sizes, true));
    
	$form_fields['art_type'] = array(
		'label' => 'Art Information',
		'input' => 'html',
		'html' => "<label for='is_art'>Art item for sale? </label><input name='attachments[{$post->ID}][is_art]' type='checkbox' id='is_art' ".($is_art?"checked='true'":"")."/>
                            <div id='art_info' style='display: none;'>
                            <label for='attachments[{$post->ID}][art_type]'>Art Category:</label>
                                <select name='attachments[{$post->ID}][art_type]' id='art_type'>
                                  <option value='paint'".($current_type == 'paint'?' selected':'').">Paint</option>
                                  <option value='sculpture'".($current_type == 'sculpture'?' selected':'').">Sculpture</option>
                                  <option value='mixed'".($current_type == 'mixed'?' selected':'').">Mixed Media</option>
                                  <option value='photo'".($current_type == 'photo'?' selected':'').">Photography</option>
                                </select><br />
                                
                                <span id='art_price_disp'>
                                    <label for='art_price'>Art Price:</label><input name='attachments[{$post->ID}][art_price]' type='text' id='art_price' value='".($price?$price:"0")."' /><br />
                                </span>
                                <style  type='text/css'>
                                    #photo_prices_disp input.photo_sizes{
                                        width:2em;
                                        }
                                </style>
                                <span id='photo_prices_disp' style='display:none;'>
                                    <label for='photo_prices0'>Photo Price:</label><input name='attachments[{$post->ID}][art_prices][0]' type='text' id='photo_prices0' value='".($prices?$prices[0]:"75")."' />
                                    <label for='photo_sizes00'>Photo Size:</label><input name='attachments[{$post->ID}][art_sizes][0][width]' type='text' id='photo_sizes00' class='photo_sizes' value='".($sizes?$sizes[0]['width']:"7")."' />\"
                                    <label for='photo_sizes01'>by</label><input name='attachments[{$post->ID}][art_sizes][0][height]' type='text' id='photo_sizes01' class='photo_sizes' value='".($sizes?$sizes[0]['height']:"10")."' />\"<br />
                                    <label for='photo_prices1'>Photo Price:</label><input name='attachments[{$post->ID}][art_prices][1]' type='text' id='photo_prices1' value='".($prices?$prices[1]:"150")."' />
                                    <label for='photo_sizes10'>Photo Size:</label><input name='attachments[{$post->ID}][art_sizes][1][width]' type='text' id='photo_sizes10' class='photo_sizes' value='".($sizes?$sizes[1]['width']:"10")."' />\"
                                    <label for='photo_sizes11'>by</label><input name='attachments[{$post->ID}][art_sizes][1][height]' type='text' id='photo_sizes11' class='photo_sizes' value='".($sizes?$sizes[1]['height']:"16")."' />\"<br />
                                    <label for='photo_prices2'>Photo Price:</label><input name='attachments[{$post->ID}][art_prices][2]' type='text' id='photo_prices2' value='".($prices?$prices[2]:"250")."' />
                                    <label for='photo_sizes20'>Photo Size:</label><input name='attachments[{$post->ID}][art_sizes][2][width]' type='text' id='photo_sizes20' class='photo_sizes' value='".($sizes?$sizes[2]['width']:"13")."' />\"
                                    <label for='photo_sizes21'>by</label><input name='attachments[{$post->ID}][art_sizes][2][height]' type='text' id='photo_sizes21' class='photo_sizes' value='".($sizes?$sizes[2]['height']:"18")."' />\"<br />
                                </span>
                                <lable for='button_id'>PayPal Button ID: </lable><input id='button_id' type='text' value='".get_post_meta($post->ID, 'PPButtonID', TRUE)."' disabled='disabled' />
                            </div>",
		'helps' => 'Specify the art medium or type.',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'attachment_art_info', 10, 2 );

/* Save art info meta data and setup button information. */
function attachment_art_info_save( $post, $attachment ) {
    //error_log(print_r($attachment, true));
    if($attachment['is_art']){
        $paypalService = new PayPalAPIInterfaceServiceService();
        $buttonID = get_post_meta($post['ID'], 'PPButtonID', TRUE);
        $button = "";
        $button = get_button($paypalService, $buttonID);
        
        if($attachment["art_type"] != "photo"){
            if(get_post_meta($post['ID'], 'IsTestButton', TRUE) || $buttonID && $button->Ack=='Success'){
                $button = update_button($paypalService, $button, $post['post_title'], $attachment['art_price']);
            }
            else{
                $button = make_button($paypalService, $post['post_title'], $attachment['art_price']);
            }
        }
        else{
            if(get_post_meta($post['ID'], 'IsTestButton', TRUE) || $buttonID && $button->Ack=='Success'){
                $button = update_option_button($paypalService, $button, $post['post_title']);
            }
            else{
                $button = make_option_button($paypalService, $post['post_title']);
            }
        }
        
        update_post_meta($post['ID'], 'IsTestButton', IS_TESTING);
        if($button->Ack != 'Failure'){
            update_post_meta($post['ID'], 'PPButtonID', $button->HostedButtonID);
        }
        else{
            echo "Error creating/updating paypal button. Check logs for more details.";
            error_log($button, 3, ERROR_FILE);
        }
        update_post_meta( $post['ID'], 'art_type', $attachment['art_type'] );
        update_post_meta( $post['ID'], 'art_price', $attachment['art_price'] );
        update_post_meta( $post['ID'], 'photo_prices', $attachment['art_prices']);
        update_post_meta( $post['ID'], 'photo_sizes', $attachment['art_sizes']);
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
					"business=" . DEPLOY_EMAIL,
					"amount=" . $price,
                                        "tax_rate=" . TAX_RATE,
                                        "shipping=" . FLAT_SHIPPING);
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
					"amount=" . $price,
                                        "tax_rate=" . TAX_RATE,
                                        "shipping=" . FLAT_SHIPPING);
        
        $request = new BMUpdateButtonReq();
        $request->BMUpdateButtonRequest = $requestType;
        try {
            return $paypalService->BMUpdateButton($request);
        } catch (Exception $ex) {
            require '../Error.php';
        }
    }
    
    function make_option_button(){}
    function update_option_button(){}
?>