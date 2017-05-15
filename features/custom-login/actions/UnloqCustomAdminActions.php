<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.2.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqCustomAdminActions
 */
class UnloqCustomAdminActions extends UnloqCustomAdminFilters
{
    const MENU_HOOK_SLUG = 'wpunloq';
    const SUBMENU_SECTION_SLUG = 'unloq-custom-admin-path';
    const SECTION_TITLE = 'Custom Login Path';

    /**
     * UnloqCustomAdmin constructor
     */
    public function __construct()
    {

        add_action('plugins_loaded', array($this, 'loadTextDomain'), 9);
        if (is_multisite() && !function_exists('is_plugin_active_for_network') || !function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }

        if ($this->isIncompatible()) {
            $this->addIncompatibleActions();
        }
        if ($this->isInConflict()) {
            $this->deactivate();
        }
        if ($this->isIncompatible() || $this->isInConflict()) {
            return;
        }

        register_activation_hook($this->basename(), array($this, 'activate'));

        if ($this->isMultisite()) {
            $this->addWpmuActions();
            add_filter('network_admin_plugin_action_links_' . $this->basename(),
                array($this, 'plugin_action_links'));
        }
        $this->addWordpressInitActions();


        $this->updatePathOptions();
        $this->addChangePathNoticeActions();
        $this->addFilters();

        add_action('unloq_custom_admin_path_settings',
            array($this, 'unloqSectionDescription'));
        add_action('unloq_custom_admin_path_settings',
            array($this, 'renderInput'));
        add_action('admin_menu', array($this, 'unloqCustomAdminPath'));

        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
    }

    public function activate()
    {
        add_option($this->getOptionPrefix() . '_redirect', '1');
        delete_option($this->getOptionPrefix() . '_admin');
    }

    /**
     * add change path notice actions
     */
    public function addChangePathNoticeActions()
    {
        add_action('unloq_notices', array($this, 'adminNoticeChangePath'));
        add_action('network_admin_notices',
            array($this, 'adminNoticeChangePath'));
    }

    /**
     * check conflicts
     * @return bool
     */
    public function isInConflict()
    {
        return is_plugin_active_for_network('rename-wp-login/rename-wp-login.php')
            || is_plugin_active('rename-wp-login/rename-wp-login.php');
    }

    /**
     * add incompatible actions - notices
     */
    public function addIncompatibleActions()
    {
        add_action('unloq_actions', array($this, 'adminNoticesIncompatible'));
        add_action('network_admin_notices',
            array($this, 'adminNoticesIncompatible'));
    }

    /**
     * deactivate current plugin due to conflict
     */
    public function deactivate()
    {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('unloq_actions',
            array($this, 'adminNoticesPluginConflict'));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }

    /**
     * check compatibility
     * @return bool
     */
    protected function isIncompatible()
    {
        global $wp_version;
        return version_compare($wp_version, '4.0-RC1-src', '<');
    }

    /**
     * add submenu
     */
    function unloqCustomAdminPath()
    {
        add_submenu_page(
            UnloqCustomAdminActions::MENU_HOOK_SLUG,
            '',
            UnloqCustomAdminActions::SECTION_TITLE,
            'manage_options',
            UnloqCustomAdminActions::SUBMENU_SECTION_SLUG,
            array($this, 'unloqCustomAdminPathPageHtml')
        );
    }

    function unloqCustomAdminPathPageHtml()
    {
        do_action('unloq_save_settings');
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        do_action('unloq_notices');
        ?>
        <div class="wrap">
            <h1><?php echo UnloqCustomAdminActions::SECTION_TITLE ?></h1>
            <div class="card unloq-card">
                <h1><?= esc_html(get_admin_page_title()); ?></h1>
                <form action="admin.php?page=<?php echo UnloqCustomAdminActions::SUBMENU_SECTION_SLUG ?>"
                      method="post">
                    <?php
                    do_action('unloq_custom_admin_path_settings');
                    // output security fields for the registered setting "wporg_options"
                    settings_fields(UnloqCustomAdminActions::SUBMENU_SECTION_SLUG . '_options');
                    // output setting sections and their fields
                    // (sections are registered for "wporg", each field is registered to a specific section)
                    do_settings_sections(UnloqCustomAdminActions::SUBMENU_SECTION_SLUG);
                    // output save settings button

                    ?>
                    <div style="text-align: right">
                        <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
        </div>

        <?php
    }

    /**
     * add description
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
}