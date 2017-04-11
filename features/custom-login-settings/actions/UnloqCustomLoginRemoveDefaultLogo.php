<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.2.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

class UnloqCustomLoginRemoveDefaultLogo {

    /**
     * UnloqCustomLoginRemoveDefaultLogo constructor.
     */
    public function __construct()
    {
        add_action( 'login_enqueue_scripts', array($this, 'customLogo') );
    }

    /**
     *
     */
    public function customLogo() { ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                <?php
                    $unloqLoginLogo = get_site_option('unloq__login_logo');
                     if($unloqLoginLogo) {
                        echo "background-image: url('".$unloqLoginLogo."');'";
                     }
                ?>
                height:65px;
                background-size: contain;
                background-repeat: no-repeat;
                padding-bottom: 30px;
            }
            <?php
                $unloqBodyColor = get_site_option('unloq__login_body_color');
                $unloqLoginFormColor = get_site_option('unloq__login_box_color');
                $unloqLoginLabel = get_site_option('unloq__login_text_color');
                if($unloqBodyColor) {
                    echo "body { background:".$unloqBodyColor." !important; }";
                }
                if($unloqLoginFormColor) {
                    echo "#loginform { background: ".$unloqLoginFormColor." !important; }";
                    echo ".tabs.unloq-active .unloq-login-box { background: ".$unloqLoginFormColor." !important; }";
                }
                if($unloqLoginLabel) {
                    echo ".login label { color: ".$unloqLoginLabel." !important; }";
                }
            ?>
        </style>
    <?php }
}


