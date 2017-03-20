<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * adds wpmu support to unloq custom admin path
 * Class UnloqWpmuSupport
 */
class UnloqWpmuSupport extends UnloqSingleSiteSupport
{
    /**
     * check if multisite
     * @return bool
     */
    protected function isMultisite()
    {
        return (is_multisite() && is_plugin_active_for_network($this->basename()));
    }

    /**
     * add WPMU options
     */
    public function addWpmuActions()
    {
        add_action('wpmu_options', array($this, 'wpmuOptionsForm'));
        add_action('update_wpmu_options', array($this, 'updateWpmuOptions'));
    }

    /**
     * TODO add wpmu options form
     */
    public function wpmuOptionsForm()
    {
    }

    /**
     * TODO update wpmu options in admin - save settings
     *
     */
    public function updateWpmuOptions()
    {
    }
}
