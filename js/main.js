const { generateQRCode } = require('./common');

jQuery( document ).ready( function() {
  jQuery( 'a.generate-qr-code-link' ).on( 'click', function ( e ) {
    e.preventDefault();
    jQuery(this).addClass('disabled');
    generateQRCode(jQuery(this).closest('.gcc-qr-code--meta-box-wrapper'), 'generate', jQuery(this).hasClass('with-logo'), null, cleanUpLabels);
  });

  jQuery( 'a.regenerate-qr-code-link' ).on( 'click', function ( e ) {
    let target = jQuery(this);
    removingOldQRCode(target);
    generateQRCode(target.closest('.gcc-qr-code--meta-box-wrapper.regenerate'), 'regenerate', jQuery(this).hasClass('with-logo'), null, cleanUpLabels);
  });
});

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
