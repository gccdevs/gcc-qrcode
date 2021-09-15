const { generateQRCode } = require('./ajax');

const showImage = (target, response, type, reload) => {
  const $settingPage = jQuery('.generate-qr-code-settings');

  if (response && response.image) {
    const link = `<a download href="${ response.image }"><img src="${ response.image }" id="qr-code-${ response.id }" /></a>`
    $settingPage.find('.generate-here .result').append(link)
  }
}

jQuery( document ).ready( function() {
  const $settingPage = jQuery('.generate-qr-code-settings');

  $settingPage.find( '.generate-here button.generate' ).on( 'click', function ( e ) {
    e.preventDefault();
    const data = $settingPage.find('.generate-here textarea.content').val()
    if (data) {
      generateQRCode($settingPage.find('.generate-here .result'), 'generate-flush', jQuery(this).hasClass('with-logo'), data, showImage);
    }
  });
});
