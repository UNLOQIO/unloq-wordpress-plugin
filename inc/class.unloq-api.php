<?php

class UnloqApi
{
    private $key;
    private $secret;
    const API_URL = "https://api.unloq.io";
    const API_VERSION = "1";
    const PLUGIN_LOGIN = "https://plugin.unloq.io/login.js";
    const HOOK_LOGIN = "/?unloq_uauth=login";
    const HOOK_LOGOUT = "/?unloq_uauth=logout";

    public function __construct($key = null, $secret = null) {
        if (!$key) {
            $key = UnloqConfig::get('api_key');
        }
        if (!$secret) {
            $secret = UnloqConfig::get('api_secret');
        }
        $this->key = $key;
        $this->secret = $secret;
    }

    /*
     * Helper function, returns the full API path along with the given path
     * */
    private function getPath($path, $withVersion = true) {
        if (!$withVersion) {
            return UnloqApi::API_URL . $path;
        }
        $full = UnloqApi::API_URL . '/v' . UnloqApi::API_VERSION . $path;
        return $full;
    }


    /*
     * Performs an API call to the given path
     * */
    protected function request($method = "GET", $path, $data = null, $includeVersion = true) {
        $url = $this->getPath($path, $includeVersion);
        $args = array('timeout' => 5, 'redirection' => 0, 'user-agent' => 'unloq-wordpress', 'headers' => array('X-Api-Key' => $this->key, 'X-Api-Secret' => $this->secret));
        if ($method == "GET") {
            $res = wp_remote_get($url, $args);
        } else {
            if ($method === "POST") {
                if ($data) {
                    $args['body'] = $data;
                }
                $res = wp_remote_post($url, $args);
            }
        }
        $resp = new UnloqError();
        if (!is_array($res)) {
            $resp->error();
            return $resp;
        }
        $body = $res['body'];
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['type'])) {
            $resp->error();
            return $resp;
        }
        if ($data['type'] != 'success') {
            $resp->error($data);
            return $resp;
        }
        $resp->success($data);
        return $resp;
    }

    /*
     * Tests the current API credentials. This is a short GET request, to validate
     * */
    public function test() {
        $res = $this->request("GET", "/credentials", null, false);
        return $res;
    }

    /*
     * Returns the current public login/logout hooks
     * */
    public function getHook($which) {
        $fullUrl = get_site_url();
        $fullPath = UnloqUtil::getUrlPath($fullUrl);
        switch($which) {
            case "login":
                return $fullPath . UnloqApi::HOOK_LOGIN;
            case "logout":
                return $fullPath . UnloqApi::HOOK_LOGOUT;
            default:
                return null;
        }
    }

    /*
     * Updates the two webhooks that UAUTH uses: /uauth/login and /uauth/logout
     * */
    public function updateHooks($loginPath = null, $logoutPath = null) {
        if ($loginPath == null) {
            $loginPath = $this->getHook('login');
        }
        if ($logoutPath == null) {
            $logoutPath = $this->getHook('logout');
        }
        $res = $this->request("POST", "/settings/webhooks", array('login' => $loginPath, 'logout' => $logoutPath));
        return $res;
    }


    /*
     * Tries and fetches attached information of the UAuth access token.
     * */
    public function getLoginToken($token, $sid = null, $duration = null) {
        if (!is_string($token) || strlen($token) < 129) {
            return new UnloqError("The UNLOQ access token is not valid or expired. Please try again.", "ACCESS_TOKEN");
        }
        $data = array("token" => $token);
        if (is_string($sid)) {
            $data['sid'] = $sid;
        }
        if ($duration != null) {
            $data['duration'] = $duration;
        }
        $res = $this->request("POST", "/token", $data);
        if (!$res->error) {
            // We verify the data integrity.
            if (!isset($res->data['id']) || !isset($res->data['email'])) {
                return new UnloqError("The UNLOQ auth token does not contain login information. Please try again.", "API_ERROR");
            }
        }
        return $res;
    }
    /*
     * Verifies that the given assoc array's signature.
    * 1. Create a string with the URL PATH(PATH ONLY), including QS and the first/
    * 2. Sort the data alphabetically,
    * 3. Append each KEY,VALUE to the string
    * 4. HMAC-SHA256 with the app's api secret
    * 5. Base64-encode the signature.
     * */
    public function verifySignature($path, $data, $signature = null) {
        if($signature == null) {    // We take it from headers.
            $headers = getallheaders();
            if(!isset($headers['X-Unloq-Signature']) || !isset($headers['X-Requested-With'])) {
                return false;
            }
            $signature = $headers['X-Unloq-Signature'];
        }
        if(!is_string($path) || !is_array($data)) return false;
        if(substr($path, 0, 1) !== "/") {
            try {
                $tmp = parse_url($path);
                if ($tmp) {
                    $path = $tmp['path'];
                    if($tmp['query']) {
                        $path = $path.'?'.$tmp['query'];
                    }
                } else {
                    $path = '/' . $path;
                }
            } catch (Exception $e) {
                $path = '/' . $path;
            }
        }
        $sorted = array();
        foreach($data as $key => $value) {
            if($key == "unloq_uauth") continue;
            array_push($sorted, $key);
        }
        asort($sorted);
        foreach($sorted as $key) {
            $val = (isset($data[$key]) ? $data[$key] : '');
            if(!is_string($val)) $val = "";
            $path = $path. $key . $val;
        }
        $apiSecret = UnloqConfig::get('api_secret');
        $hash = hash_hmac("sha256", $path, $apiSecret, true);
        $finalHash = base64_encode($hash);
        if($finalHash !== $signature) return false;
        return true;
    }
}