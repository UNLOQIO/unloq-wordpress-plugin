<?php
    UnloqUtil::register_style('admin');
    UnloqUtil::register_js('admin');
?>

<div class="wrap">
    <h2>UNLOQ Setup</h2>
    <div class="card unloq-card">
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
    </div>
</div>