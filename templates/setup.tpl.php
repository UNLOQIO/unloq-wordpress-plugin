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
            <li>2. Create and verify the domain <b><?php echo $domain; ?></b></li>
            <li>3. Create an application with this domain</li>
            <li>4. Configure the application</li>
            <li>5. Enter the API Key and API Secret of your app bellow.</li>
        </ul>
        <form id="unloq-form" method="post">
            <?php wp_nonce_field('unloq_setup'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th class="option-key" scope="row">
                        <label for="unloqApiKey">API Key</label>
                    </th>
                    <td colspan="2">
                        <input type="text" id="unloqApiKey" name="api_key" value="<?php echo (UnloqUtil::body('api_key') ? UnloqUtil::body('api_key') : UnloqConfig::get('api_key')) ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="option-key" scope="row">
                        <label for="unloqApiSecret">API Secret</label>
                    </th>
                    <td colspan="2">
                        <input type="password" id="unloqApiSecret" name="api_secret" value=""/>
                    </td>
                </tr>
            </table>
            <br/>
            <?php submit_button("Setup"); ?>
        </form>
        <br/>
        <p>
            For more information about UNLOQ authentication, visit our <a href="http://unloq.readme.io/" target="_blank">documentation</a>.
        </p>
    </div>
</div>