<?php

class UnloqUAuth
{

    private static $instance;
    private static $binded = false;

    public static function bind()
    {
        // If the plugin is enabled, we hook into the redirection system.
        if (!UnloqConfig::isActive() || !UnloqConfig::isSetup()) {
            return;
        }
        self::$binded = true;
        self::$instance = new self();
    }

    public function __construct()
    {
        if (!self::$binded) {
            return;
        }
        /* We capture the uauth login/logout redirects from the querystring. */
        require_once(UNLOQ_PATH . "inc/class.login.php");
        $login = new UnloqLogin();
        add_action("generate_rewrite_rules", array($this, "rewrite_rules"));
        add_action('init', array($login, 'init'), 1);
        add_action("init", array($this, "parse_request"), 1);
    }

    /* Makes sure that /uauth/login and /uauth/logout can be rewritten. */
    public function rewrite_rules($wp_rewrite)
    {
        $new_rules = array('uauth/login' => 'index.php?unloq_uauth=login', 'uauth/logout' => 'index.php?unloq_uauth=logout');
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    /* Looks into the request and checks if we have any login/logout custom redirect. */
    public function parse_request()
    {
        $qs = UnloqUtil::getQuery();
        if (!array_key_exists('unloq_uauth', $qs)) {
            return;
        }
        $action = $qs['unloq_uauth'];
        // If we have uauth=login, the user's coming with an access token back.
        if ($action == "login" && UnloqUtil::isGet()) {
            $this->accessToken($qs['token']);
            return;
        }
        if ($action == "logout" && UnloqUtil::isPost()) {
            $this->logout();
            return;
        }
    }

    /*
     * Creates a new user.
     * */
    private function _createUser($data, $unloqId)
    {
        // Registration disabled.
        if (!get_option('users_can_register')) {
            UnloqUtil::flash('Registration is currently disabled.');
            return false;
        }
        if (isset($data['username'])) {
            $username = $data['username'];
        } else {
            $username = str_replace("@", ".", $data['email']);
            $username = str_replace("_", ".", $username);
        }
        $exists = username_exists($username);
        if ($exists) {
            $username .= rand(1000, 9999);
        }
        // We have to generate a 64-random char password.
        $userPass = UnloqUtil::generateString(64);
        $newUser = new stdClass();
        $newUser->user_email = $data['email'];
        $newUser->user_login = $username;
        $newUser->user_pass = $userPass;
        $newUser->unloq_id = $unloqId;
        if (isset($data['first_name'])) {
            $newUser->first_name = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $newUser->last_name = $data['last_name'];
        }
        $registered = wp_insert_user($newUser);
        if (is_wp_error($registered)) {
            UnloqUtil::flash('Failed to create the new user.');
            return false;
        }
        $user = get_user_by('email', $data['email']);
        if (is_wp_error($user)) {
            UnloqUtil::flash('Failed to perform registration, could not read user');
            return false;
        }
        // Finally, we try and update the unloq_id.
        $ok = $this->_updateUnloqId($user, $unloqId);
        if (is_wp_error($ok)) {
            UnloqUtil::flash('Failed to perform authentication, could not update user.');
            return false;
        }
        // User was created, we return OK
        return $user;
    }

    private function _updateUnloqId($user, $unloqId)
    {
        // We perform the update.
        if ($user->unloq_id == $unloqId) {
            return true;
        }
        global $wpdb;
        $table = $wpdb->prefix . "users";
        return $wpdb->update($table, array(
            'unloq_id' => $unloqId
        ), array(
            'ID' => $user->ID
        ));
    }

    private function _updateUnloqSecret($unloqId, $secret)
    {
        global $wpdb;
        $table = $wpdb->prefix . "users";
        if (!$unloqId) return false;

        if ($secret == null) {
            $prep = $wpdb->prepare("UPDATE $table SET unloq_secret = NULL WHERE unloq_id = %s AND unloq_secret IS NOT NULL LIMIT 1", $unloqId);
        } else {
            $prep = $wpdb->prepare("UPDATE $table SET unloq_secret = %s WHERE unloq_id = %s AND unloq_secret IS NULL LIMIT 1", $secret, $unloqId);
        }
        $res = $wpdb->query($prep);
        if (is_wp_error($res)) {
            return false;
        }
        return true;
    }

    /* Reads the given user by his email. If not found, we try and create him. */
    private function _readUser($data, $unloqId)
    {
        $email = $data['email'];
        $user = get_user_by('email', $email);
        // IF we have a user, we update him.
        if ($user) {
            $ok = $this->_updateUnloqId($user, $unloqId);
            if (is_wp_error($ok)) {
                UnloqUtil::flash('Failed to perform authentication, could not read user.');
                return false;
            }
            return $user;
        }
        // IF we do not have the user, we try and create him.
        return $this->_createUser($data, $unloqId);
    }

    /*
     * Reads a user by his unloqId
     * */
    private function _readUserByUID($unloqId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . "users";
        $res = $wpdb->get_results($wpdb->prepare("SELECT id FROM $tableName WHERE unloq_id='%s' LIMIT 1", $unloqId));
        if (is_wp_error($res) || !is_array($res) || sizeof($res) !== 1) {
            return null;
        }
        $userId = $res[0]->id;
        if (!isset($userId)) return null;
        $user = get_user_by('id', $userId);
        if (is_wp_error($user) || !$user) {
            return null;
        }
        return $user;
    }


    /* Performs access token login */
    private function accessToken($token)
    {
        if (!$token) {
            UnloqUtil::flash("The UAuth access token is missing.");
            wp_redirect(wp_login_url());
            exit;
        }
        // At this point, we will call UNLOQ to get info.
        $api = new UnloqApi();
        $res = $api->getLoginToken($token, session_id());
        if ($res->error) {
            switch ($res->code) {
                case "ACCESS_TOKEN.EXPIRED":
                    UnloqUtil::flash('It took your browser too much time to finalize the login. Please try again.');
                    break;
                default:
                    UnloqUtil::flash($res->message);
            }
            wp_redirect(wp_login_url());
            exit;
        }
        $requested_redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
        if (!$requested_redirect_to) {
            $flash = UnloqUtil::tempFlash("redirect_to");
            if ($flash) {
                $requested_redirect_to = $flash;
            }
        }
        // Once we're here, we got the user data in the $res->data field. Primarily, we have $res->data['id'] and $res->data['email']
        $unloqId = strval($res->data['id']);
        $user = $this->_readUser($res->data, $unloqId);
        if ($user === false) {
            wp_redirect(wp_login_url());
            exit;
        }
        $reauth = empty($_REQUEST['reauth']) ? false : true;
        if($reauth) {
            wp_clear_auth_cookie();
        }
        // user created/read, we log him in
        $secure_cookie = is_ssl();
        if (FORCE_SSL_ADMIN) {
            $secure_cookie = true;
            force_ssl_admin(true);
        }
        wp_set_auth_cookie($user->ID, false, $secure_cookie);
        wp_set_current_user($user->ID, $user->user_login);
        $redirect_to = apply_filters('login_redirect', admin_url(), $requested_redirect_to, $user);
        if (!is_string($redirect_to) || $redirect_to == "") {
            $redirect_to = site_url();
        }
        wp_redirect($redirect_to);
        exit();
    }

    /*
     * This function performs remote logout. It is called by UNLOQ when a user chose to
     * logout from his device.
     * This is the POST LOGOUT WEBHOOK, found in the UNLOQ documentation
     * */
    private function logout()
    {
        $sid = UnloqUtil::body('sid');
        $apiKey = UnloqUtil::body('key');
        $unloqId = UnloqUtil::body('id');
        if (!$sid || !$apiKey || !$unloqId) {
            status_header(500);
            echo "Invalid logout action, missing data.";
            exit;
        }
        if (UnloqConfig::get('api_key') !== $apiKey) {
            status_header(500);
            echo 'Invalid logout action, key missmatch.';
            exit;
        }
        $api = new UnloqApi();
        $logoutHook = $api->getHook('logout');
        if (!$api->verifySignature($logoutHook, $_POST)) {
            status_header(500);
            echo "Invalid logout signature.";
            exit;
        }
        // Once we've reached this part, we can query for the user.
        $user = $this->_readUserByUID($unloqId);
        if (!$user) {
            status_header(404);
            echo "User not found";
            exit;
        }
        $sessions = WP_Session_Tokens::get_instance($user->ID);
        $sessions->destroy_all();
        if (isset($_SESSION)) {
            session_destroy();
        }
        session_id($sid);
        session_start();
        if (!session_destroy()) {
            status_header(500);
            echo "Failed to destroy session.";
            exit;
        }
        status_header(200);
        echo json_encode(array(
            'status' => true
        ));
        exit;
    }


}

?>