<?php

add_action('admin_menu', 'addPluginAdminMenu');


function addPluginAdminMenu() {
  register_setting('qr_code_settings', 'qr_code_settings', 'qr_code_settings_validate');
  add_menu_page('QR Code Generator', 'QR Code Generator', 'administrator', 'qr-code-settings', 'displayPluginAdminDashboard', 'dashicons-admin-links');
}

function qr_code_settings_validate($args) {
  return $args;
}

function displayPluginAdminDashboard() {
  ?>
  <div class="generate-qr-code-settings">
    <div class="container">
      <h1>Generate QR Code</h1>
      <hr>
      <div class="global-settings">
        <h2>Global Settings</h2>
        <form class="form-group" action="options.php" method="post">
          <?php
            settings_fields( 'qr_code_settings' );
            do_settings_sections( __FILE__ );
            $options = get_option( 'qr_code_settings' );
          ?>
          <div class="form-control">
            <label class="label">Logo URL</label>
            <div class="form-field">
              <input class="form-control" name="qr_code_settings[logo_uri]" type="text" id="logo_uri" value="<?php echo (isset($options['logo_uri']) && $options['logo_uri'] != '') ? $options['logo_uri'] : ''; ?>"/>
            </div>
            <span class="description">Please enter a valid logo link</span>
          </div>
          <br>
          <input class="button button-primary" type="submit" value="Save" />
        </form>
      <br>
      <hr>
      <div class="generate-here form-group">
        <h2>Generate now</h2>
        <div class="form-control">
          <label class="label" for="content">Content</label>
          <div class="form-field">
            <textarea class="content" name="content" cols="30" rows="10"></textarea>
            <br>
            <span class="description">This can be a link or a paragraph</span>
          </div>
          <br>
          <button class="button button-primary generate">Generate</button>
          <button class="button button-secondary generate with-logo">Generate with logo</button>
        </div>
        <div class="result mt-2"></div>
      </div>
    </div>
  </div>
  <?php
}
