<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqCustomAdminBase
 */
class UnloqCustomAdminBase
{
    /**
     * custom admin path slug option name
     */
    const SLUG_OPTION = 'unloq_custom_admin_url';

    /**
     * default slug option value
     */
    const DEFAULT_SLUG_LOGIN = 'wp-login.php';

    /**
     * options prefix
     */
    const OPTION_PREFIX = 'unloq_cl';

    /**
     * show not found when going to default wp-admin / wp-login wordpress
     */
    protected function showNotFound()
    {
        global $wp_query;
        $wp_query->set_404();
        get_template_part(404);
        die();
    }

    /**
     * get base name
     * @return string
     */
    protected function basename()
    {
        return plugin_basename(__FILE__);
    }

    /**
     * @return string
     */
    protected function getOptionPrefix()
    {
        return UnloqCustomAdminBase::OPTION_PREFIX;
    }

    /**
     * @param null $scheme
     * @return string
     */
    public function getNewLoginUrl($scheme = null)
    {
        $slug = $this->newLoginSlug();
        if ($slug === 'wp-login.php') {
            return home_url('/', $scheme) . $slug;
        }
        if ($this->getPermalinkStructure()) {
            return $this->userTrailingslashit(home_url('/',
                    $scheme) . $slug);
        }
        return home_url('/', $scheme) . '?' . $slug;
    }

    /**
     * get login slug simple / multisite / defalut
     * @return string
     */
    protected function newLoginSlug()
    {
        if ($slug = $this->getSlugOption()) {
            return $slug;
        } else {
            if ($this->isMultisite() && $slug = $this->getSiteSlugOption()) {
                return $slug;
            }
        }
        return UnloqCustomAdminBase::DEFAULT_SLUG_LOGIN;
    }

    /**
     * @return bool
     */
    protected function useTrailingSlashes()
    {
        return ('/' === substr($this->getPermalinkStructure(), -1, 1));
    }

    /**
     * @param $string
     * @return string
     */
    protected function userTrailingslashit($string)
    {
        return $this->useTrailingSlashes()
            ? trailingslashit($string)
            : untrailingslashit($string);
    }

    /**
     * get login slug name
     * @return string
     */
    protected function getSlugOption()
    {
        return get_option(UnloqCustomAdminBase::SLUG_OPTION);
    }

    /**
     * @return string
     */
    protected function getSiteSlugOption()
    {
        return get_site_option(UnloqCustomAdminBase::SLUG_OPTION, UnloqCustomAdminBase::DEFAULT_SLUG_LOGIN);
    }

    /**
     * @return array
     */
    public function forbiddenSlugs()
    {
        $wp = new WP;
        return array_merge($wp->public_query_vars, $wp->private_query_vars);
    }

    /**
     * @return string
     */
    protected function getPermalinkStructure()
    {
        return get_option('permalink_structure');
    }

    /**
     * check if is wp sign up section
     * @return bool
     */
    protected function isWpSignUp()
    {
        return strpos($this->getServerRequestUri(), 'wp-signup') !== false;
    }

    /**
     * check if is wp activate section
     * @return bool
     */
    protected function isWpActivate()
    {
        return strpos($this->getServerRequestUri(), 'wp-activate') !== false;
    }

    /**
     * @return string
     */
    protected function getServerRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
