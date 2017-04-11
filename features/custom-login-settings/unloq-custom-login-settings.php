<?php
require 'actions/UnloqCustomLoginRemoveDefaultLogo.php';
require 'actions/UnloqCustomLoginSettings.php';

/**
 * Class UnloqCustomAdminSettings
 */
class UnloqCustomAdminSettings  extends UnloqCustomLoginSettings {
    /**
     * @var UnloqCustomAdmin
     */
    protected static $instance = null;

    /**
     * Return instance
     * @return   UnloqCustomAdmin
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}