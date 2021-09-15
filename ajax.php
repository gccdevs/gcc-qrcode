<?php

add_action("wp_ajax_gcc_qr_code_generate", 'handleGenerateQRCode' );
add_action("wp_ajax_nopriv_gcc_qr_code_generate", 'handleGenerateQRCode' );

function handleGenerateQRCode() {
  $type = array_get( $_POST, 'type' );
  $post_type = array_get( $_POST, 'name' );
  $post_id = array_get( $_POST, 'id' );
  $withLogo = array_get( $_POST, 'withLogo' );
  if(!$post_type) {
    exit;
  }
  if(!$post_id) {
    exit;
  }

  switch ($type) {
    case 'generate-flush':
      echo json_encode([
        'url' => 'here',
        'status' => 200
      ]);
      $content = array_get( $_POST, 'content' );
      if (!$content) {
        exit;
      }
      flushQRCode($content, $withLogo);
      break;
    default:
      $data = get_permalink($post_id);
      $path = generateQRCode($data, $post_type, $post_id, $withLogo);
      echo json_encode([
        'url' => wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $path,
        'status' => 200
      ]);
      break;
  }
  exit;
}

function flushQRCode($data, $withLogo) {
  $options = get_option( 'qr_code_settings' );
  echo 'setting: ' . $options;
  echo '$withLogo: ' . $withLogo;
  echo 'here: ' . $data;
}

function generateQRCode( $data, $post_type, $post_id, $showLogo = false ) {
  if( !$data ) {
    return;
  }
  $base = wp_upload_dir();
  $path = $base['basedir'] . '/qr-code-pngs';

  if(!file_exists($path)) {
    mkdir($path, 0777, true);
  }
  $filename = '/qrcode_'. $post_type . '_' . $post_id . '.png';

  $absolutePath = $path . $filename;

  if(file_exists($absolutePath)) {
    @unlink($absolutePath);
  }

  try {
    QRcode::png($data , $absolutePath, QR_ECLEVEL_H, 40, 2);
  } catch (\Exception $e) {
    return 'Failed to generate qr code';
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

  $logoPath = 'https://glorycitychurch.com/wp-content/uploads/2019/01/GC-LOGO-150x150.png';
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
  $logo_qr_width = $QR_width / 4;
  $logo_qr_height = $logo_height / 4;

  $result = imagecopyresampled(
    $QR,
    $logo,
    ($QR_width - $logo_qr_width) / 2,
    ($QR_height - $logo_qr_height) / 2,
    0,
    0,
    $logo_qr_width,
    $logo_qr_height,
    $logo_width,
    $logo_height
  );

  imagepng($QR, $path);
}
