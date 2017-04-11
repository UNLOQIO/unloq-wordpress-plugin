<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.4.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

/**
 * Class UnloqCustomAdminNotices
 */
class UnloqCustomAdminNotices extends UnloqTextDomain
{
    /**
     * Error conflict message with rename wp-login plugin
     * https://wordpress.org/plugins/rename-wp-login/
     */
    const ERROR_CONFLICT = 'UNLOQ.io authentication could not be activated because 
                            you already have Rename wp-login.php active. 
                            Please uninstall Rename wp-login.php to use Unloq Custom Admin Path';

    /**
     * Error incompatible message wordpress version
     */
    const ERROR_INCOMPATIBLE = 'Please upgrade to the latest version of 
                                WordPress to enable <strong>UNLOQ.io custom login path</strong>';

    const ADMIN_NOTICE_CHANGE_PATH = 'Your login page is now here: <strong><a href="%1$s">%2$s</a></strong>';

    const ADMIN_HELP_DESCRIPTION = 'Found a bug? contact us on <a href="https://unloq.io" target="_blank">unloq.io</a>.';

    /**
     * add admin notice incompatible
     */
    public function adminNoticesIncompatible()
    {
        echo $this->addErrorNotice($this::ERROR_INCOMPATIBLE);
    }

    /**
     * add admin notice when path is changed
     */
    public function adminNoticeChangePath()
    {
        global $pagenow;
        $slug = $this->newLoginSlug();
        if (!is_network_admin()
            && isset($_GET['page'])
            && $_GET['page'] == 'unloq-custom-admin-path'
            && $slug !== 'wp-login.php'
        ) {
            echo '<div class="updated notice is-dismissible"><p>' .
                sprintf($this->translate($this::ADMIN_NOTICE_CHANGE_PATH),
                    $this->getNewLoginUrl(),
                    $this->getNewLoginUrl())
                . '</p></div>';
        }
    }

    /**
     * add admin notice plugin conflict
     */
    public function adminNoticesPluginConflict()
    {
        echo $this->addErrorNotice($this::ERROR_CONFLICT);
    }

    /**
     * add translatable error notice
     * @param $message
     * @return string
     */
    public function addErrorNotice($message)
    {
        return "
        <div class=\"error notice is-dismissible\">
            <p>{$this->translate($message)}</p>
        </div>
        ";
    }
}