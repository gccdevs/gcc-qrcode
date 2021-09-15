const { generateQRCode } = require('./ajax');

jQuery( document ).ready( function() {
  jQuery( 'a.generate-qr-code-link' ).on( 'click', function ( e ) {
    e.preventDefault();
    jQuery( 'a.regenerate-qr-code-link' ).addClass('hide');
    removingOldQRCode();
    generateQRCode(jQuery(this).closest('.gcc-qr-code--meta-box-wrapper'), 'generate', jQuery(this).hasClass('with-logo'), null, cleanUpLabels);
  });

  jQuery( 'a.regenerate-qr-code-link' ).on( 'click', function ( e ) {
    let target = jQuery(this);
    removingOldQRCode();
    generateQRCode(target.closest('.gcc-qr-code--meta-box-wrapper.regenerate'), 'regenerate', jQuery(this).hasClass('with-logo'), null, cleanUpLabels);
  });
});

function removingOldQRCode() {
  const target = jQuery( 'a.regenerate-qr-code-link' )
  target.addClass( 'hide' );
  const generating = '<p>Generating...</p>';
  target.parent().append(generating);
  let parent = target.closest('.gcc-qr-code--meta-box-wrapper.regenerate');
  parent.find('a.qrcode-img-wrapper').remove();
}

function cleanUpLabels() {
  jQuery( 'a.regenerate-qr-code-link' ).removeClass('hide');
  location.reload(true);
}
