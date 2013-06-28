jQuery(document).ready(function(){
    jQuery('#is_art').is(":checked")?jQuery('#art_info').show():jQuery('#art_info').hide();
    
    jQuery('#is_art').change(function(){
        this.checked?jQuery('#art_info').show():jQuery('#art_info').hide();
    });
    
    if(jQuery("#art_type").val() != 'photo'){
        jQuery("#photo_prices_disp").hide();
        jQuery("#art_price_disp").show();
    }
    else{
        jQuery("#photo_prices_disp").show();
        jQuery("#art_price_disp").hide();
    }
    
    jQuery("#art_type").change(function(){
        if(jQuery(this).val() != "photo"){
            jQuery("#photo_prices_disp").hide();
            jQuery("#art_price_disp").show();
        }
        else{
            jQuery("#photo_prices_disp").show();
            jQuery("#art_price_disp").hide();
        }
    });
});

function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }