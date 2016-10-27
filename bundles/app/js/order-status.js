(function($){
	
	//====== Modules ======//
    var mobMenu = require('./modules/mob-menu'),
    	modal = require('./modules/modal'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order');

    // Close modal
    modal.closeModal();

    $('.btn-pay').on('click', function(e){
        e.preventDefault();

        $('#form-payment').submit();
    });

})(jQuery);