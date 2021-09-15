const { generateQRCode } = require('./common');

const cleanUp = target => {

}

jQuery( document ).ready( function() {
  const $settingPage = jQuery('.generate-qr-code-settings');

  $settingPage.find( '.generate-here button.generate' ).on( 'click', function ( e ) {
    e.preventDefault();
    const data = $settingPage.find('.generate-here textarea.content').val()
    if (data) {
      generateQRCode($settingPage.find('.generate-here .result'), 'generate-flush', jQuery(this).hasClass('with-logo'), data, cleanUp);
    }
  });
});
