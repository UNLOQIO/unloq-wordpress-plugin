<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqTextDomain
 */
class UnloqTextDomain extends UnloqCustomAdminBase
{
    const LOCALE_REGISTRATION = 'unloq-login';

    /**
     * load text domain function
     */
    public function loadTextDomain()
    {
        load_plugin_textdomain(UnloqTextDomain::LOCALE_REGISTRATION, false,
            dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * translate string
     * @param string $string
     * @return string|void
     */
    protected function translate($string = '')
    {
        return __($string, UnloqTextDomain::LOCALE_REGISTRATION);
    }

}