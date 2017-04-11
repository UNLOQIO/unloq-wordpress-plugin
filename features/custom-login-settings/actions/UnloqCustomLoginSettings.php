<?php

/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.2.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */
class UnloqCustomLoginSettings extends UnloqCustomLoginRemoveDefaultLogo
{

    const SUBMENU_SECTION_SLUG = 'unloq-custom-admin-path';

    public function __construct()
    {
        parent::__construct();
        add_action('unloq_custom_admin_path_settings', array($this, 'addForm'), 20);
        add_action('unloq_save_settings', array($this, 'updateSettings'));
        add_action('admin_enqueue_scripts', array($this, 'addEnqueueMedia'));
    }

    /**
     * add media uploader support
     */
    public function addEnqueueMedia()
    {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        }
        // Css rules for Color Picker
        wp_enqueue_style( 'wp-color-picker' );
        // Register javascript
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
        // Make sure to add the wp-color-picker dependecy to js file
        wp_enqueue_script( 'cpa_custom_js', plugins_url( 'assets/js/jquery.colorpicker.js', UNLOQ_PLUGIN_PATH), array( 'jquery', 'wp-color-picker' ), '', true  );
    }

    /**
     * update path option
     */
    public function updateSettings()
    {
        if ($_GET['page'] == $this::SUBMENU_SECTION_SLUG) {
            if ($_POST['unloq__login_logo'] && ($unloq_custom_admin_logo = $_POST['unloq__login_logo'])) {
                update_site_option('unloq__login_logo',
                    $unloq_custom_admin_logo);
            }
            if(isset($_POST['unloq__login_body_color'])) {
                update_site_option('unloq__login_body_color', $_POST['unloq__login_body_color']);
            }
            if(isset($_POST['unloq__login_box_color'])) {
                update_site_option('unloq__login_box_color', $_POST['unloq__login_box_color']);
            }
            if(isset($_POST['unloq__login_text_color'])) {
                update_site_option('unloq__login_text_color', $_POST['unloq__login_text_color']);
            }
        }

    }

    /**
     * add form
     */
    public function addForm()
    {
        include 'form.php';
    }
}