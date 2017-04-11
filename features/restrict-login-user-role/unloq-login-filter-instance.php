<?php
require 'UnloqLoginFilter.php';

/**
 * Class UnloqCustomAdminSettings
 */
class UnloqLoginFilterInstance  extends UnloqLoginFilter {

    /**
     * @var UnloqLoginFilter
     */
    protected static $instance = null;

    /**
     * Return instance
     * @return   UnloqLoginFilter
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
