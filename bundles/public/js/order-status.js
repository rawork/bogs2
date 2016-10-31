(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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
},{"./modules/call-order":2,"./modules/catalog-order":3,"./modules/mob-menu":4,"./modules/modal":5}],2:[function(require,module,exports){
'use strict';
//====== Call order ======//
var modal = require('./modal');
module.exports = (function() {
    $(document).on('click', '.show-call-order', function(e){
        e.preventDefault();
        $('.modal .title').html('Заказать звонок');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn call-order">Заказать</button></form>');
        modal.showModal();
        yaCounter29093585.reachGoal('show_order_call');
    });

    $(document).on('click', '.call-order', function(e){
        e.preventDefault();
        var name = $('input#name').val();
        var phone = $('input#phone').val();

        if (name === '' || phone === '') {
            return;
        }

        $.post("/ajax/call", {name: name, phone: phone},
            function(data){
                $('.modal .content').html(data.text);
                yaCounter29093585.reachGoal('order_call');
            }, "json");
    });
})();
},{"./modal":5}],3:[function(require,module,exports){
'use strict';
//====== Catalog Order ======//
var modal = require('./modal');
module.exports = (function(){
	$(document).on('click', '.show-catalog-order', function(e){
        e.preventDefault();
        $('.modal .title').html('BOGS Оптом');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="email" placeholder="Ваш e-mail"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn catalog-order">Заказать</button></form>');
        modal.showModal();
        yaCounter29093585.reachGoal('show_order_pdf');
    });

    $(document).on('click', '.catalog-order', function(e){
        e.preventDefault();

        var name = $('input#name').val();
        var email = $('input#email').val();
        var phone = $('input#phone').val();

        if (name === '' || phone === '' || email === '') {
            return;
        }

        $.post("/ajax/catalog", {name: name, email: email, phone: phone},
            function(data){
                $('.modal .content').html(data.text);
                yaCounter29093585.reachGoal('order_pdf');
            }, "json");
    });
})();
},{"./modal":5}],4:[function(require,module,exports){
'use strict';
//====== Mobile menu ======//
module.exports = (function(){
    var header2 = $('#header2'),
        showCat = $('#header1 .pull-right').html(),
        menuLinks = $('.menu ul').html(),
        socialLinks = $('.social').html(),
        mmBtn = $('.mm-btn'),
        mobMenuCont = $('.mm-cont');

    mobMenuCont.html('<ul>' + menuLinks + '</ul>' + showCat + '<div class="social">' + socialLinks + '</div>');

    mmBtn.on('click', function(e){
        e.preventDefault();
        header2.toggleClass('show-menu');
    })
}());

},{}],5:[function(require,module,exports){
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
},{}]},{},[1])