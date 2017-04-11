<?php
/**
 * Plugin Name: UNLOQ.io authentication
 * Plugin URI: https://unloq.io
 * Version: 1.2.0
 * Author: UNLOQ.io
 * Description: Perform UNLOQ.io authentications with the click of a button
 */

require 'actions/base/UnloqCustomAdminBase.php';
require 'actions/text-domain/UnloqTextDomain.php';
require 'actions/notices/UnloqCustomAdminNotices.php';
require 'actions/support/UnloqSingleSiteSupport.php';
require 'actions/support/UnloqWpmuSupport.php';
require 'actions/filters/UnloqCustomAdminFilters.php';
require 'actions/UnloqCustomAdminActions.php';

/**
 * Class UnloqCustomAdmin
 */
class UnloqCustomAdmin extends UnloqCustomAdminActions
{
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
