<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqCustomAdminFilters
 */
class UnloqCustomAdminFilters extends UnloqWpmuSupport
{
    /**
     * add filters to
     */
    public function addFilters()
    {
        add_filter('plugin_action_links_' . $this->basename(), array($this, 'pluginActionLinks'));
        if (!class_exists('ITSEC_Core')) {
            add_filter('site_url', array($this, 'siteUrl'), 10, 4);
            add_filter('network_site_url', array($this, 'networkSiteUrl'), 10, 3);
            add_filter('wp_redirect', array($this, 'wpRedirect'), 10, 2);
            add_filter('site_option_welcome_email', array($this, 'welcomeEmail'));
        }
    }

    /**
     * adds settings to modules enable / disable page
     * @param $links
     * @return array
     */
    public function pluginActionLinks($links)
    {
        if (is_network_admin() && is_plugin_active_for_network($this->basename())) {
            array_unshift($links,
                '<a href="' . network_admin_url('admin.php?page=unloq-custom-admin-path') . '">' .
                $this->translate('Settings')
                . '</a>');
        } elseif (!is_network_admin()) {
            array_unshift($links,
                '<a href="' . admin_url('options-general.php#unloq-custom-admin-path') . '">' .
                $this->translate('Settings')
                . '</a>');
        }
        return $links;
    }

    /**
     * get site url
     * @param $url
     * @param $path
     * @param $scheme
     * @param $blog_id
     * @return string
     */
    public function siteUrl($url, $path, $scheme, $blog_id)
    {
        return $this->filterWpLoginPhp($url, $scheme);
    }

    /**
     * get network site url
     * @param $url
     * @param $path
     * @param $scheme
     * @return string
     */
    public function networkSiteUrl($url, $path, $scheme)
    {
        return $this->filterWpLoginPhp($url, $scheme);
    }

    /**
     * @param $location
     * @param $status
     * @return mixed
     */
    public function wpRedirect($location, $status)
    {
        return $this->filterWpLoginPhp($location);
    }

    /**
     * adds custom slug to welcome email - wpmu
     * @param $value
     * @return mixed
     */
    public function welcomeEmail($value)
    {
        return $value = str_replace('wp-login.php',
            trailingslashit($this->getSiteSlugOption()), $value);
    }

    /**
     * filter wordpress login url, adds support for ssl certificate as well
     * @param $url
     * @param null $scheme
     * @return string
     */
    public function filterWpLoginPhp($url, $scheme = null)
    {
        if (strpos($url, 'wp-login.php') !== false) {
            if (is_ssl()) {
                $scheme = 'https';
            }
            $args = explode('?', $url);
            if (isset($args[1])) {
                parse_str($args[1], $args);
                $url = add_query_arg($args, $this->getNewLoginUrl($scheme));
            } else {
                $url = $this->getNewLoginUrl($scheme);
            }
        }
        return $url;
    }

}