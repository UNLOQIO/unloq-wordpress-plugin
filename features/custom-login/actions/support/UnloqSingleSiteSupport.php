<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqSingleSiteSupport
 */
class UnloqSingleSiteSupport extends UnloqCustomAdminNotices
{
    /**
     * @var bool
     */
    protected $wp_login_php;

    const TITLE = 'Unloq - Admin Login url';

    const WPMU_HELP_DESCRIPTION = 'To set a networkwide default, go to <a href="%s">Network Settings</a>.';

    /**
     * add admin init / plugins loaded / wp loaded actions
     */
    public function addWordpressInitActions()
    {
        add_action('admin_init', array($this, 'adminInit'), 2);
        add_action('plugins_loaded', array($this, 'pluginsLoaded'), 2);
        add_action('wp_loaded', array($this, 'wpLoaded'));
    }

    /**
     * update path option
     */
    public function updatePathOptions()
    {
        if ($_GET['page'] == $this::SUBMENU_SECTION_SLUG) {
            if ($_POST['unloq_custom_admin_url'] && ($unloq_custom_admin_url = sanitize_title_with_dashes($_POST['unloq_custom_admin_url']))
                && strpos($unloq_custom_admin_url, 'wp-login') === false
                && !in_array($unloq_custom_admin_url, $this->forbiddenSlugs())
            ) {
                update_site_option('unloq_custom_admin_url',
                    $unloq_custom_admin_url);
            }
        }
    }

    /**
     * render input in admin settings
     */
    public function renderInput()
    {
        if ($this->getPermalinkStructure()) {
            echo '<code>' . trailingslashit(home_url()) . '</code> 
                <input id="unloq_custom_admin_url" type="text" name="unloq_custom_admin_url" value="' . $this->newLoginSlug() . '">' . ($this->useTrailingSlashes()
                    ? ' <code>/</code>' : '');
        } else {
            echo '<code>' . trailingslashit(home_url()) . '?</code> 
                <input id="unloq_custom_admin_url" type="text" name="unloq_custom_admin_url" value="' . $this->newLoginSlug() . '">';
        }
    }

    /**
     * section description
     */
    public function unloqSectionDescription()
    {
        $out = '';
        if (!is_multisite() || is_super_admin()) {
            $out .= $this->translate($this::ADMIN_HELP_DESCRIPTION);
        }

        if (is_multisite() && is_super_admin() && is_plugin_active_for_network($this->basename())) {
            $out .= sprintf($this->translate($this::WPMU_HELP_DESCRIPTION),
                $this->getNetwordAdminUrlSettings());
        }
        echo "<p>$out</p>";

    }

    /**
     * get network admin url settings
     * @return string
     */
    public function getNetwordAdminUrlSettings()
    {
        return network_admin_url('settings.php#unloq-custom-admin-path-input');
    }

    /**
     * add admin settings for options
     */
    public function adminInit()
    {
        add_settings_section('wps-hide-login-section', $this::TITLE,
            array($this, 'unloqSectionDescription'), 'general');
        add_settings_field('unloq_custom_admin_url',
            '<label for="unloq_custom_admin_url">' . $this->translate('Login url') . '</label>',
            array($this, 'renderInput'), 'general', 'wps-hide-login-section');

        register_setting('general', 'unloq_custom_admin_url',
            'sanitize_title_with_dashes');

        if (get_option('unloq_custom_admin_redirect')) {
            delete_option('unloq_custom_admin_redirect');
            if (is_multisite()
                && is_super_admin()
                && is_plugin_active_for_network($this->basename())
            ) {
                $redirect = network_admin_url('settings.php#unloq-custom-admin-path-input');
            } else {
                $redirect = admin_url('options-general.php#unloq-custom-admin-path-input');
            }
            wp_safe_redirect($redirect);
            die;
        }
    }

    /**
     * plugin loaded
     */
    public function pluginsLoaded()
    {
        global $pagenow;
        if (!is_multisite() && ($this->isWpSignUp() || $this->isWpActivate()) !== false) {
            $this->showNotFound();
        }

        $request = parse_url($_SERVER['REQUEST_URI']);
        if ((strpos($_SERVER['REQUEST_URI'],
                    'wp-login.php') !== false || untrailingslashit($request['path']) === site_url('wp-login',
                    'relative')) && !is_admin()
        ) {
            $this->wp_login_php = true;
            $_SERVER['REQUEST_URI'] = $this->userTrailingslashit('/' . str_repeat('-/',
                    10));
            $pagenow = 'index.php';

        } elseif (untrailingslashit($request['path']) === home_url($this->newLoginSlug(),
                'relative')
            || (!$this->getPermalinkStructure()
                && isset($_GET[$this->newLoginSlug()])
                && empty($_GET[$this->newLoginSlug()]))
        ) {
            $pagenow = 'wp-login.php';
        }
    }

    /**
     * block wp-admin, wp-login.php and show not found page
     */
    public function wpLoaded()
    {
        global $pagenow;
        if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX') && $pagenow !== 'admin-post.php') {
            $this->showNotFound();
        }
        $request = parse_url($_SERVER['REQUEST_URI']);
        if ($pagenow === 'wp-login.php' && $request['path'] !== $this->userTrailingslashit($request['path']) && $this->getPermalinkStructure()) {
            wp_safe_redirect($this->userTrailingslashit($this->getNewLoginUrl()) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
            die;
        } elseif ($this->wp_login_php) {
            if (($referer = wp_get_referer())
                && strpos($referer, 'wp-activate.php') !== false
                && ($referer = parse_url($referer))
                && !empty($referer['query'])
            ) {
                parse_str($referer['query'], $referer);
                if (!empty($referer['key'])
                    && ($result = wpmu_activate_signup($referer['key']))
                    && is_wp_error($result)
                    && ($result->get_error_code() === 'already_active'
                        || $result->get_error_code() === 'blog_taken')
                ) {
                    wp_safe_redirect($this->getNewLoginUrl()
                        . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    die;
                }
            }
            $this->wpTemplateLoader();
        } elseif ($pagenow === 'wp-login.php') {
            global $error, $interim_login, $action, $user_login;
            @require_once ABSPATH . 'wp-login.php';
            die;
        }
    }

    /**
     * template loader
     */
    protected function wpTemplateLoader()
    {
        global $pagenow;
        $pagenow = 'index.php';
        if (!defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', true);
        }
        wp();
        if ($this->getServerRequestUri() ===
            $this->userTrailingslashit(str_repeat('-/', 10))
        ) {
            $_SERVER['REQUEST_URI'] = $this->userTrailingslashit('/wp-login-php/');
        }
        require_once(ABSPATH . WPINC . '/template-loader.php');
        die;
    }
}
