<?php

?>
<style>
    .header_logo_upload {
        width: 100%;
        text-align: center;
        bottom: -30px;
        position: absolute;
        left: 0;
        padding: 5px !important;
        line-height: 100% !important;
        margin: 0px !important;

    }

    .header_logo_upload_area {
        cursor: pointer;
        height: 60px;
        width: 60px;
        border: 1px dashed dimgray;
        background: #ddd;
        position: relative;
        margin-bottom: 40px;
        text-align: center;
    }
    .header_logo {
        margin-top: 10px;
    }

    .customise-colors label {
        width: 137px;
        display: block;
        float: left;
        height: 24px;
        line-height: 24px;
    }
</style>
<h4>Login page customisation</h4>
<div style="float: left;">
    <p><strong>Login page image</strong>
</div>
<div class="header_logo_upload_area" style="margin-left: 138px;">
    <img class="header_logo"
         src="<?php echo get_option('unloq__login_logo'); ?>"
         height="40"/>
    <a href="#" class="header_logo_upload button button-secondary">Upload</a>
</div>

<input class="header_logo_url" type="text" name="unloq__login_logo" size="60"
       value="<?php echo get_option('unloq__login_logo'); ?>"
       style="display: none">
</p>


<div class="customise-colors">
    <label for="body_color">
        Background Color
    </label>
    <input type="text" id="body_color" name="unloq__login_body_color" class="unloq-color-picker" value="<?php echo get_site_option('unloq__login_body_color') ?>">
</div>
<div class="customise-colors">
    <label for="body_color">
        Login Box Color
    </label>
    <input type="text" id="body_color" name="unloq__login_box_color" class="unloq-color-picker" value="<?php echo get_site_option('unloq__login_box_color') ?>">
</div>
<div class="customise-colors">
    <label for="body_color">
        Text Color
    </label>
    <input type="text" id="body_color" name="unloq__login_text_color" class="unloq-color-picker" value="<?php echo get_site_option('unloq__login_text_color') ?>">
</div>
<br />
<script>
    jQuery(document).ready(function ($) {
        $('.header_logo_upload, .header_logo_upload_area').click(function (e) {
            e.preventDefault();

            var custom_uploader = wp.media({
                title   : 'Custom Image',
                button  : {
                    text: 'Upload Image'
                },
                multiple: false  // Set this to true to allow multiple files to be selected
            })
                .on('select', function () {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('.header_logo').attr('src', attachment.url);
                    $('.header_logo_url').val(attachment.url);

                })
                .open();
        });
    });
</script>