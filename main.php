<?php

add_action( 'add_meta_boxes', 'addQRCodeMetaBox' );
add_action("wp_ajax_gcc_qr_code_generate", 'handleGenerateQRCode' );
add_action("wp_ajax_nopriv_gcc_qr_code_generate", 'handleGenerateQRCode' );

function addQRCodeMetaBox() {
  $postTypes = get_post_types();
  if (!$postTypes) {
    return;
  }
  foreach ($postTypes as $screen) {
    if( $screen != 'nav_menu_item' &&
    $screen != 'acf-field' &&
    $screen != 'acf-field-group' &&
    $screen != 'schema' &&
    $screen != 'wp_block' &&
    $screen != 'user_request' &&
    $screen != 'customize_changeset' &&
    $screen != 'custom_css' &&
    $screen != 'oembed_cache' &&
    $screen != 'revision' ) {
      if(isset($_GET['action']) && $_GET['action'] == 'edit') {
        add_meta_box( 'gcc-qr-code-for-' . $screen, 'Generate QR Code', 'generateQRCodeHTML', $screen, 'side', 'low' );
      }
    }
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
  $path = generateQRCode($data, $post_type, $post_id, $type, true);
  echo json_encode([
    'url' => wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $path,
    'status' => 200 ]);
  exit;
}

function generateQRCode( $data, $post_type, $post_id, $type, $showLogo = false ) {
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
    try {
      QRcode::png($data , $absolutePath, QR_ECLEVEL_H);
    } catch (\Exception $e) {
      return 'Failed to generate QR code';
    }
  } else if($type == 'regenerate') {
    try {
      @unlink($absolutePath);
      QRcode::png($data , $absolutePath, QR_ECLEVEL_H);
    } catch( \Exception $e) {
      return 'Failed to regenerate QR code';
    }
  }

  if ($showLogo) {
    try {
      addLogo($absolutePath, $filename);
    } catch( \Exception $e) {
      return 'Failed to attach logo to QR code';
    }
  }

  return $filename;
}

function addLogo ($path, $filename) {

  $logoPath = 'https://www.chromatix.com.au/assets/themes/chromatix-2018-child/dist/favicon/favicon-16x16.png';
  $logo = imagecreatefrompng($logoPath);
  $QR = imagecreatefrompng(wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $filename);
  $QR_width = imagesx($QR);
  $QR_height = imagesy($QR);
  $logo_width = imagesx($logo);
  $logo_height = imagesy($logo);

  if (!$QR_width || !$QR_height) {
    throw new Error('Invalid QR code size');
  }

  if (!$logo_width || !$logo_height || $logo_width > $QR_width) {
    throw new Error('Invalid logo size');
  }

  // Scale logo to fit in the QR Code
  $logo_qr_width = $QR_width / 3;
  $scale = $logo_width / $logo_qr_width;
  $logo_qr_height = $logo_height / $scale;

  $result = imagecopyresampled(
    $QR,
    $logo,
    $QR_width / 3,
    $QR_height / 3,
    0,
    0,
    $logo_qr_width,
    $logo_qr_height,
    $logo_width,
    $logo_height
  );

  echo '$result: ' . $result;
  echo '$QR: ' . $QR;

  imagepng($QR, $path);
}
