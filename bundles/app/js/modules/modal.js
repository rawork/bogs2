'use strict';
module.exports = {
    showModal: function (){
        var that = this;
        var body = $('body');
        $('.modal').removeClass('hidden');
        body.addClass('no-scroll');
        if ($('.modal .gallery-img-big img').length) { // Wait when big image loaded
            $('.modal .gallery-img-big img').load(function(){
                that.getMarginTop();
                $( '.modal .gallery-img-big img' ).unbind( 'load' );
            });
        } else {
            that.getMarginTop();
        }
    },
    getMarginTop: function(){
        var modalDialog = $('.modal-dialog'),
            winHeight = $(window).outerHeight(),
            modalDialogHeight = modalDialog.outerHeight(),
            marginTop = ( modalDialogHeight + 50 ) < winHeight ? ( winHeight - modalDialogHeight ) / 2 : 50;
        $('.modal-dialog').css({
            marginTop: marginTop    
        });
    },
    closeModal: function(){
        $(document).on('click', '.modal .close, .modal-overlay', function(e){
            e.preventDefault();
            $('body').removeClass('no-scroll');
            $(this).parents('.modal').addClass('hidden').removeClass('modal--product');
        });
    }
}