<?php

/**
 * Class LoginFilter
 */
class UnloqLoginFilter
{
    /**
     * add any roles to block from any path besides
     * wp-admin or custom path for admin login
     */
    public static $RESTRICTED_ROLES = array(
        'administrator',
        'editor',
        'author'
    );

    /**
     * LoginFilter constructor.
     */
    public function __construct()
    {
        add_filter('authenticate', array($this, 'filterLoginFrontend'), 30, 3);
    }

    /**
     * @param WP_User $user
     * @param $username
     * @param $password
     * @return WP_Error
     */
    public function filterLoginFrontend($user, $username, $password)
    {
        if(isset($user->errors)) return $user;
        if(!UnloqConfig::isActive()) {
            return $user;
        }
        // Force unloq-only login.
        if(UnloqConfig::isUnloqOnly()) {
            return null;
        }
        if (!$this->isAdminLoginPage() && UnloqConfig::isUnloqOnly()) {
            foreach ($this::$RESTRICTED_ROLES as $role) {
                foreach ($user->roles as $userRole) {
                    if ($userRole == $role) {
                        return null;
                    }
                }
            }
        }
        return $user;
    }

    /**
     * check if its the admin page
     * @return bool
     */
    protected function isAdminLoginPage()
    {
        if ($GLOBALS['pagenow'] === 'wp-login.php') {
            return true;
        }
        return false;
    }
}
