<?php

add_action('add_meta_boxes', 'addQRCodeMetaBox' );

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

  $filename = 'qrcode_' . $post_type . '_' . $post_id . '.png';
  $file = wp_upload_dir()['basedir'] . '/qr-code-pngs' . '/' . $filename;

  if(file_exists($file)) {
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
    <a href="javascript:void(0);" class="regenerate-qr-code-link with-logo">Regenerate (With logo)</a>
  </div>
  <?php
}

function createNewCodeTemplate($post) {
  ?>
  <div class="gcc-qr-code--meta-box-wrapper generate" data-name="<?= $post->post_type ?>" data-id="<?= $post->ID ?>">
    <a href="javascript:void(0);" class="generate-qr-code-link">
      Generate
    </a>
    <a href="javascript:void(0);" class="generate-qr-code-link with-logo">
      Generate (with logo)
    </a>
  </div>
  <?php
}
