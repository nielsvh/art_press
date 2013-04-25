jQuery(document).ready(function(){
    jQuery('#is_art').is(":checked")?jQuery('#art_info').show():jQuery('#art_info').hide();
    
    jQuery('#is_art').change(function(){
        this.checked?jQuery('#art_info').show():jQuery('#art_info').hide();
    });
});