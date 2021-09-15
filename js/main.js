jQuery( document ).ready( function() {

  jQuery( 'a.generate-qr-code-link' ).on( 'click', function ( e ) {
    e.preventDefault();
    jQuery(this).addClass('disabled');
    generateQRCode(jQuery(this).closest('.gcc-qr-code--meta-box-wrapper'), 'generate', cleanUpLabels);
  });

  jQuery( 'a.regenerate-qr-code-link' ).on( 'click', function ( e ) {
    let target = jQuery(this);
    removingOldQRCode(target);
    generateQRCode(target.closest('.gcc-qr-code--meta-box-wrapper.regenerate'), 'regenerate', cleanUpLabels);
  });

});

function generateQRCode(target, type, callback = null) {
  let data = {
    name: target.data( 'name' ),
    id: target.data( 'id' ),
    type: type,
    action: 'gcc_qr_code_generate',
  }

  jQuery.ajax({
    url: window.ajaxurl,
    method: 'POST',
    dataType: 'json',
    data: data,
  }).always(function(response) {
    let src = '<a class="qrcode-img-wrapper ' + type + '" href="'+ response.url + '" target="_blank"><img src="' + response.url + '"></a>';
    target.prepend(src);

    if(callback) {
      callback(target, response, type);
    }
  });
}

function removingOldQRCode(target) {
  target.addClass( 'disabled' );
  target.text( 'generating...' );
  let parent = target.closest('.gcc-qr-code--meta-box-wrapper.regenerate');
  parent.find('a.qrcode-img-wrapper').remove();
}

function cleanUpLabels(target, response, type) {
  target.find('.generate-qr-code-link').addClass('regenerate-qr-code-link').removeClass( 'generate-qr-code-link' );
  target.find('.regenerate-qr-code-link').removeClass( 'disabled' );
  target.find('.regenerate-qr-code-link').text( 'Regenerate' );
  target.addClass('regenerate').removeClass('generate');
  if (type === 'generate') {
    location.reload(true);
  }
}
