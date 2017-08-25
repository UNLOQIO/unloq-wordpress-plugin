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

    const TITLE = 'UNLOQ - Admin Login url';

    const WPMU_HELP_DESCRIPTION = 'To set a networkwide default, go to <a href="%s">Network Settings</a>.';

    /**
     * add admin init / plugins loaded / wp loaded actions
     */
    public function addWordpressInitActions()
    {
        add_action('admin_init', array($this, 'adminInit'), 2);
        if (!class_exists('ITSEC_Core')) {
            add_action('plugins_loaded', array($this, 'pluginsLoaded'), 2);
            add_action('wp_loaded', array($this, 'wpLoaded'));
        }
    }

    public function validateCustomPath($value)
    {
        if ($value === 'wp-login.php' || $value === 'wp-login-php') return "wp-login.php";
        return sanitize_title_with_dashes($value);
    }

    public function validateCustomExpose($val)
    {
        if ($val === "0") return "false";
        return "true";
    }

    /**
     * update path option
     */
    public function updatePathOptions()
    {
        if (isset($_GET['page']) && $_GET['page'] == UnloqCustomAdminActions::SUBMENU_SECTION_SLUG) {
            if (isset($_POST['unloq_custom_admin_url']) && ($unloq_custom_admin_url = sanitize_title_with_dashes($_POST['unloq_custom_admin_url']))
                && !in_array($unloq_custom_admin_url, $this->forbiddenSlugs())
            ) {
                update_site_option('unloq_custom_admin_url', $this->validateCustomPath($unloq_custom_admin_url));
            }
        }
    }

    /**
     * render input in admin settings
     */
    public function renderInput()
    {
        $slug = $this->newLoginSlug();
        if ($this->getPermalinkStructure()) {
            echo '<code>' . trailingslashit(home_url()) . '</code> 
                <input id="unloq_custom_admin_url" type="text" name="unloq_custom_admin_url" value="' . $this->newLoginSlug() . '">' . ($this->useTrailingSlashes() && $slug !== 'wp-login.php'
                    ? ' <code>/</code>' : '');
        } else {
            echo '<code>' . trailingslashit(home_url()) . ($slug === 'wp-login.php' ? '' : '?'). '</code> 
                <input id="unloq_custom_admin_url" type="text" name="unloq_custom_admin_url" value="' . $this->newLoginSlug() . '">';
        }
    }

    /*
     * Render expose wp-admin
     * */
    public function renderExposeInput()
    {
        $val = get_option("unloq_custom_admin_expose");
        if ($val === false) $val = "true";   // defaults to yes.
        echo '<select name="unloq_custom_admin_expose" id="unloq_custom_admin_expose">
                 <option value="1" ' . ($val == "true" ? 'selected="selected"' : '') . '>Yes</option>
                 <option value="0" ' . ($val == "false" ? 'selected="selected"' : '') . '>No</option>
               </select>';
    }

    /**
     * section description
     */
    public function unloqSectionDescription()
    {
        $out = '';
        if (!is_multisite() || is_super_admin()) {
            $out .= $this->translate(self::ADMIN_HELP_DESCRIPTION);
        }

        if (is_multisite() && is_super_admin() && is_plugin_active_for_network($this->basename())) {
            $out .= sprintf($this->translate(self::WPMU_HELP_DESCRIPTION),
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
        // Add the default login url path
        add_settings_section('wps-hide-login-section', self::TITLE,
            array($this, 'unloqSectionDescription'), 'general');
        if (!class_exists('ITSEC_Core')) {
            add_settings_field('unloq_custom_admin_url',
                '<label for="unloq_custom_admin_url">' . $this->translate('Login url') . '</label>',
                array($this, 'renderInput'), 'general', 'wps-hide-login-section');
        }

        register_setting('general', 'unloq_custom_admin_url',
            array($this, 'validateCustomPath'));

        // Add the "Do not expose wp-admin/" option
        add_settings_field('unloq_custom_admin_expose',
            '<label for="unloq_custom_admin_expose">' . $this->translate('Expose wp-admin/') . '</label>',
            array($this, 'renderExposeInput'), 'general', 'wps-hide-login-section');
        register_setting('general', 'unloq_custom_admin_expose',
            array($this, 'validateCustomExpose'));

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
        $slug = $this->newLoginSlug();
        if($slug === 'wp-login.php') return;
        $request = parse_url($_SERVER['REQUEST_URI']);
        if ((strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false ||
                untrailingslashit($request['path']) === site_url('wp-login', 'relative'
            )) &&
            !is_admin()
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
        $currentPage = $pagenow;
        $hasRedirect = true;
        $exposeWpAdmin = get_option("unloq_custom_admin_expose");
        $slug = $this->newLoginSlug();
        if ($exposeWpAdmin === false) $exposeWpAdmin = "true";
        if ($exposeWpAdmin === "false" && is_admin() && !is_user_logged_in() && !defined('DOING_AJAX') && $pagenow !== 'admin-post.php') {
            $this->showNotFound();
        }
        // We check for backward-compatibility with no redirect path.
        if ($slug == 'wp-login.php' && strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
            return;
        }
        $reqUri = $_SERVER['REQUEST_URI'];
        if(strpos($reqUri, "/-/-/-/-/-/-/") === 0 && isset($_SERVER['REDIRECT_URL'])) {
            $reqUri = $_SERVER['REDIRECT_URL'];
        }
        $request = parse_url($reqUri);
        try {
            $newLoginUrl = $this->getNewLoginUrl();
            $parsedLoginUrl = parse_url($newLoginUrl);
            if (isset($request['path']) && isset($parsedLoginUrl['path']) && ($request['path'] === $parsedLoginUrl['path'] || $request['path'] . '/' === $parsedLoginUrl['path'])) {
                if(!$this->getPermalinkStructure()) {
                    // We have to check if we do not permalinks. If we do not, we have to skip the parsing.
                    parse_str(isset($request['query']) ? $request['query'] : "", $reqQuery);
                    parse_str(isset($parsedLoginUrl['query']) ? $parsedLoginUrl['query'] : "", $unloqQuery);
                    if(!isset($parsedLoginUrl['query']) || !isset($reqQuery)) return;
                    if(!isset($reqQuery[$parsedLoginUrl['query']])) {
                        return;
                    }
                }
                $this->wp_login_php = true;
                global $error, $interim_login, $action, $user_login;
                @require_once ABSPATH . 'wp-login.php';
                die;
            }
        } catch (Exception $e) {
        }
        if ($currentPage === 'wp-login.php' && $request['path'] !== $this->userTrailingslashit($request['path']) && $this->getPermalinkStructure()) {
            wp_safe_redirect($this->userTrailingslashit($this->getNewLoginUrl()) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
            die;
        } elseif ($this->wp_login_php && $hasRedirect) {
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
        } elseif ($currentPage === 'wp-login.php') {
            global $error, $interim_login, $action, $user_login;
            @require_once ABSPATH . 'wp-login.php';
            if ($hasRedirect === true) {
                die;
            }
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
            $reqUri = $this->userTrailingslashit('/wp-login-php/');
            if(strpos($reqUri, "/-/-/-/-/-/-/") !== 0) {
                $_SERVER['REQUEST_URI'] = $reqUri;
            }
        }
        require_once(ABSPATH . WPINC . '/template-loader.php');
        die;
    }
}
