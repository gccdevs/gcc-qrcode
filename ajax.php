<?php

add_action("wp_ajax_gcc_qr_code_generate", 'handleGenerateQRCode' );
add_action("wp_ajax_nopriv_gcc_qr_code_generate", 'handleGenerateQRCode' );

function handleGenerateQRCode() {
  $type = array_get( $_POST, 'type' );
  $post_type = array_get( $_POST, 'name' );
  $post_id = array_get( $_POST, 'id' );
  $withLogo = array_get( $_POST, 'withLogo' );

  switch ($type) {
    case 'generate-flush':
      $content = array_get( $_POST, 'content' );
      if (!$content) {
        exit;
      }
      $prefix = 'qr_code_image';
      $id = rand(100000,999999);
      $path = generateQRCode($content, $prefix, $id, is_true($withLogo));
      $img = file_get_contents(wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $path);
      $base64 = base64_encode($img);
      remove_logo($prefix, $id);
      echo json_encode([
        'id' => $id,
        'image' => 'data:image/png;base64,' . $base64,
        'status' => 200
      ]);
    default:
      if(!$post_type) {
        exit;
      }
      if(!$post_id) {
        exit;
      }
      $data = get_permalink($post_id);
      $path = generateQRCode($data, $post_type, $post_id, is_true($withLogo));
      echo json_encode([
        'url' => wp_upload_dir()['baseurl'] . '/qr-code-pngs' . $path,
        'status' => 200
      ]);
  }
  exit;
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
    QRcode::png($data, $absolutePath, QR_ECLEVEL_H, 40, 2);
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
  $options = get_option( 'qr_code_settings' );
  $logoPath = array_get($options, 'logo_uri');
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

  $logo_qr_width = $QR_width / 4;
  $logo_qr_height = $QR_height / 4;

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

function remove_logo($post_type, $post_id) {
  $filename = '/qrcode_'. $post_type . '_' . $post_id . '.png';
  $absolutePath = wp_upload_dir()['basedir'] . '/qr-code-pngs' . $filename;
  if(file_exists($absolutePath)) {
    @unlink($absolutePath);
  }
}

function is_true($val, $return_null=false){
  $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
  return ( $boolval===null && !$return_null ? false : $boolval );
}
