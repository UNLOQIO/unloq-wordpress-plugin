<?php
    UnloqUtil::register_style('admin');
    UnloqUtil::register_js('admin');
?>
<?php
$url = get_site_url();
$url = explode("/", $url);
$domain = $url[0] . "//" . $url[2];
?>
<div class="wrap">
    <h2>UNLOQ Setup</h2>
    <div class="card unloq-card">
        <h3>Welcome!</h3>
        <p>
            If you haven't created an UNLOQ account, please do so <a href="https://account.unloq.io" target="_blank">here</a>.
        </p>

        <h4>Steps for enabling the UNLOQ plugin on this website:</h4>
        <ul>
            <li>1. Login to UNLOQ</li>
            <li>2. Create a WordPress Web Application with the domain: <b><?php echo $domain; ?></b></li>
            <li>3. Enter the provided API Key and Widget Key in the form below.</li>
        </ul>
        <p>
            <i>Note</i>: You can also customise your on-boarding experience by going your application's <i>Customise</i> menu, in the UNLOQ administration panel.
        </p>
        <p>
            <?php $current_user = wp_get_current_user(); ?>
            <i>P.S</i>: Make sure to grant your UNLOQ.io e-mail account admin rights on this site, or create an UNLOQ account with the e-mail: <b><?php echo $current_user->user_email; ?></b>.
            This is to avoid locking you out of your site.
        </p>
        <form id="unloq-form" method="post" autocomplete="off">
            <?php wp_nonce_field('unloq_setup'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th class="option-key" scope="row">
                        <label for="unloqApiSecret">API Key</label>
                    </th>
                    <td colspan="2">
                        <input type="password" id="unloqApiSecret" name="api_secret" value=""/>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="option-key" scope="row">
                        <label for="unloqApiKey">Login Widget Key</label>
                    </th>
                    <td colspan="2">
                        <?php $apiKey = esc_attr(UnloqUtil::body('api_key') ? UnloqUtil::body('api_key') : UnloqConfig::get('api_key')); ?>
                        <input type="text" id="unloqApiKey" name="api_key" value="<?php echo $apiKey; ?>"/>
                    </td>
                </tr>
            </table>
            <br/>
            <?php submit_button("Setup"); ?>
        </form>
        <br/>
        <p>
            For more information about UNLOQ authentication, visit our <a href="https://docs.unloq.io/plugins/wordpress" target="_blank">documentation</a>.
        </p>
    </div>
</div>