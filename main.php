<?php

add_action( 'admin_menu', 'addAdminMenu' );
add_action( 'add_meta_boxes', 'addQRCodeMetaBox' );
add_action("wp_ajax_gcc_qr_code_generate", 'handleGenerateQRCode' );
add_action("wp_ajax_nopriv_gcc_qr_code_generate", 'handleGenerateQRCode' );

/**
 * Adds a the new item to the admin menu.
 */
function addAdminMenu() {
  add_submenu_page( 'tools.php', 'QR Code Generator', 'QR Code Generator', 'manage_options', 'qr-code-generator', 'renderAdminPanel' );
}

function renderAdminPanel() {
  ?>
  <div class="gcc-qr-code--wrapper">
    <h1 class="gcc-qr-code--title">Generate QR Code</h1>
    <h2 class="gcc-qr-code--sub-title">Choose a Post Type</h2>
    <form class="gcc-qr-code--form" action="tools.php?page=qr-code-generator?method=generate_qrcode" method="post">
      <select id="gcc-qr-code--selector" required="true" multiple style="min-height: 120px;min-width: 150px">
        <?php
          foreach(get_post_types() as $value => $name ) {
            if( $value != 'nav_menu_item' &&
                $value != 'acf-field' &&
                $value != 'acf-field-group' &&
                $value != 'schema' &&
                $value != 'wp_block' &&
                $value != 'user_request' &&
                $value != 'customize_changeset' &&
                $value != 'custom_css' &&
                $value != 'calendar' &&
                $value != 'oembed_cache' &&
                $value != 'revision' ) {
              echo '<option value="' . $value . '">' . $name . '</option>';
            }
          }
        ?>
      </select>
      <div class="gcc-qr-code--clear clear" style="margin-bottom: 30px;"></div>
      <button class="gcc-qr-code--btn button button-primary button-hero">Generate</button>
    </form>
    <div class="gcc-qr-code--container">
        <div class="gcc-qr-code--list-wrapper">
          <h2 class="gcc-qr-code--sub-title">Results are shown below:</h2>
          <div class="gcc-qr-code--max-height-container" style="max-height: 450px;overflow: scroll">
            <div class="gcc-qr-code--inner-wrapper"></div>
          </div>
        </div>
    </div>
  </div>
  <?php
}

function addQRCodeMetaBox() {
  $screens = ['page', 'book'];
  foreach ($screens as $screen) {
    add_meta_box( 'gcc-qr-code-for-' . $screen, 'Generate QR Code', 'generateQRCodeHTML', $screen, 'side', 'low' );
  }
}

function generateQRCodeHTML() {
  global $post;

  $post_type = $post->post_type;
  $post_id = $post->ID;

  $filename = 'qrcode_' . $post_type . $post_id . '.png';

  $file = wp_upload_dir()['basedir'] . '/qr-code-pngs' . '/' . $filename;

  if( file_exists($file)) {
    showExistingCodeTemplate($post, $filename);
  } else {
    createNewCodeTemplate($post);
  }

}

function showExistingCodeTemplate($post, $filename) {
  $link = wp_upload_dir()['baseurl'] . '/qr-code-pngs' . '/' . $filename;
  ?>
  <div class="gcc-qr-code--meta-box-wrapper regenerate" data-name="<?= $post->post_type ?>" data-id="<?= $post->ID ?>">
    <a class="qrcode-img-wrapper" href="<?= $link ?>" target="_blank">
      <img src="<?= $link ?>">
    </a>
    <a href="javascript:void(0);" class="regenerate-qr-code-link">Regenerate</a>
  </div>
  <?php
}

function createNewCodeTemplate($post) {
  ?>
  <div class="gcc-qr-code--meta-box-wrapper generate" data-name="<?= $post->post_type ?>" data-id="<?= $post->ID ?>">
    <a href="javascript:void(0);" class="generate-qr-code-link">
      Generate
    </a>
  </div>
  <?php
}

function handleGenerateQRCode() {
  $type = array_get( $_POST, 'type' );
  $post_type = array_get( $_POST, 'name' );
  $post_id = array_get( $_POST, 'id' );
  if(!$post_type) {
    exit;
  }
  if(!$post_id) {
    exit;
  }

  $data = get_permalink($post_id);
  $path = generateQRCode($data, $post_type, $post_id, $type);
  echo json_encode([
    'url' => wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $path,
    'status' => 200 ]);
  exit;
}

function generateQRCode( $data, $post_type, $post_id, $type ) {
  if( !$data ) {
    return;
  }
  $base = wp_upload_dir();
  $path = $base['basedir'] . '/qr-code-pngs';

  if(!file_exists($path)) {
    mkdir($path, 0777, true);
  }
  $filename = '/qrcode_'. $post_type . $post_id . '.png';

  $absolutePath = $path . $filename;

  if(!file_exists($absolutePath)) {
    QRcode::png($data , $absolutePath);
  } else if($type == 'regenerate') {
    try {
      @unlink($absolutePath);
      QRcode::png($data , $absolutePath);
    } catch( \Exception $e) {
      return 'Failed to deleted old file';
    }
  }

  return $filename;
}
