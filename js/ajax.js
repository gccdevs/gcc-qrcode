export const generateQRCode = (target, type, withLogo, content = null, callback = null) => {
  let data = {
    name: target.data( 'name' ),
    id: target.data( 'id' ),
    type: type,
    content,
    withLogo,
    action: 'gcc_qr_code_generate',
  }

  jQuery.ajax({
    url: window.ajaxurl,
    method: 'POST',
    dataType: 'json',
    data,
  }).always(function(response) {
    if(callback) {
      callback(target, response, type, !!response.url);
    }
  });
}
