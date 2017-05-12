<?php
$unloq_script = '<script type="text/javascript" src="' . UnloqApi::PLUGIN_LOGIN . '" data-unloq-theme="' . $unloq_theme . '" data-unloq-key="' . $unloq_api_key . '"></script>';
?>
<?php
// Login with UNLOQ only
if($unloq_type == "UNLOQ") { ?>
    <div class="unloq-login-box">
        <?php echo $unloq_script; ?>
        <script type="text/javascript">
            (function() {
                if(typeof window.UNLOQ !== 'object' || !window.UNLOQ || typeof window.UNLOQ.onLogin !== 'function') return;
                window.UNLOQ.onLogin(function(data) {
                    if(typeof data !== 'object' || !data) return;
                    if(typeof data.token !== 'string' || !data.token) return;
                    var redirUrl = window.location.href;
                    redirUrl += (redirUrl.indexOf('?') === -1 ? '?' : '&');
                    redirUrl += 'unloq_uauth=login&token=' + data.token;
                    window.location.href = redirUrl;
                });
            })();
        </script>
    </div>
<?php } ?>

<?php
// Login with UNLOQ or passwords
if ($unloq_type == "UNLOQ_PASS") {
    UnloqUtil::register_js('login', 'jquery');
?>
    <div id="btnInitUnloq" class="unloq-init" data-script="<?php echo UnloqApi::PLUGIN_LOGIN; ?>" data-theme="<?php echo $unloq_theme; ?>" data-key="<?php echo $unloq_api_key; ?>"></div>

<?php } ?>