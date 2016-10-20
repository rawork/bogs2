(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';
(function($) {
    var win = $(window),
        body = $('body'),
        winHeight = win.outerHeight(),
        modalDialog = $('.modal-dialog');

    //====== Modules ======//
    var modal = require('./modules/modal'),
        mobMenu = require('./modules/mob-menu'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order');

    // Close modal
    modal.closeModal();

    $(document).on('click', '.scrollto', function(e){
        e.preventDefault();
        var el = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(el).offset().top
        }, 1000);
    });

    // Add sku to basket
    $(document).on('click', '.add-product.active', function(e) {
        e.preventDefault();
        var that = $(this);
        var id = $('#sizes option:selected').val();
        var amount = 1;//$('.amount').val();

        $.post("/api/basket/edit", {id: id, amount: amount},
            function(data){
                $('#cart').html(data.minicart);
                that.html('Оформить заказ').removeClass('active');
                yaCounter29093585.reachGoal('add_product');
            }, "json");
    });

    // share buttons
    $(document).on('click', '.social a', function(e){
        e.preventDefault();
        window.open($(this).attr('href'), 'Share BOGS-SHOP.RU', 'width=600,height=400,menubar=no,location=yes,resizable=no,scrollbars=yes,status=no');
    });

    // size table
    $(document).on('click', '.sizes a', function(e){
        e.preventDefault();
        var that = $(this);
        if (that.hasClass('active')) {
            that.parents('.link').siblings('.content').slideUp();
        } else {
            that.parents('.link').siblings('.content').slideDown();
        }
        that.toggleClass('active');
    });

    //====== Product in modal ======//
    $('.view-product, .choose-product').on('click', function(e){
        e.preventDefault();
        var title = $(this).siblings('.name').text();
        $.ajax({
            url: '/ajax/product/' + $(this).attr('data-id')
        }).done(function(data){
            $('.modal').addClass('modal--product');
            $('.modal .title').html(data.title);
            $('.modal .content').html(data.content);
            $('.amount').attr('data-value', $('#sizes option:selected').attr('data-value'));
            modal.showModal();
        });
    });

    //====== Sticky Header ======//
    var header2 = body.find('#header2'),
        headerOffsetTop = header2.offset().top;
    win.on('scroll', function(){
        if (headerOffsetTop <= win.scrollTop() ) {
            body.addClass('header-fixed');
        } else {
            body.removeClass('header-fixed');
        }
    });

    //====== Sliders ======//

    // Products sliders
    $('.slider-products').slick({
        slidesToShow: 4,
        responsive: [
            {
            breakpoint: 900,
            settings: {
                slidesToShow: 3
                }
            },{
            breakpoint: 670,
            settings: {
                slidesToShow: 2
                }
            },{
            breakpoint: 400,
            settings: {
                slidesToShow: 1
                }
            }
        ]
    });

    // Video slider
    $('.slider-video').slick();

    //====== Product gallery ======//
    $(document).on('click', '.gallery-thumb', function(e){
        e.preventDefault();
        var thumb = $(this),
            parent = thumb.parent(),
            bigImages = $('.gallery-img-big li'),
            id = thumb.attr('data-img-id') - 1;
        // Grid
        parent.siblings().each(function(){
            $(this).removeClass('active');
        });
        thumb.parent().addClass('active');
        // Big img
        bigImages.each(function(){
            $(this).removeClass('active');
        });
        $( bigImages[id] ).addClass('active');
    });

    $(document).on("change keyup input click", '.amount', function () {
        var max = parseInt($(this).attr("data-value"));

        if (this.value.match(/[^0-9]/g)) {
            this.value = this.value.replace(/[^0-9]/g, '');
        }

        if (this.value > max) {
            this.value = max;
        } else if (this.value < 1) {
            this.value = 1
        }
        $(this).trigger('check');
    });

    $(document).on("click", ".amount-dec", function () {
        var amount = parseInt($(this).siblings(".amount").val());
        if (isNaN(amount)) {
            $(this).siblings(".amount").val(1);
            return false;
        }

        if (amount > 1) {
            $(this).siblings(".amount").val(--amount);
        }
        $(this).trigger('check');
    });

    $(document).on("click", ".amount-inc", function () {
        var amount = parseInt($(this).siblings(".amount").val());

        if (isNaN(amount)) {
            $(this).siblings(".amount").val(1);
            return false;
        }

        ++amount;

        var max = parseInt($(this).siblings(".amount").attr("data-value"));
        if (amount > max) {
            $(this).siblings(".amount").val(max);
        } else {
            $(this).siblings(".amount").val(amount);
        }
        $(this).trigger('check');
    });

    $(document).on('change', '#sizes', function(e){
        $('.amount')
            .attr('data-value', $('#sizes option:selected').attr('data-value'))
            .val(1);
        $('.add-product').html('Добавить в корзину').addClass('active');
    });

    //====== Product tabs ======//
    $(document).on('click', '.product-tabs a', function(e){
        e.preventDefault();
        var tab = $(this);
        if (!tab.hasClass('active')) {
            tab.parent().siblings('.active').removeClass('active');
            tab.parent().addClass('active');
            var tabContent = $( '.product-tabs-content li[data-tab-content=' + tab.attr('data-tab') + ']' );
            tabContent.siblings('.active').removeClass('active');
            tabContent.addClass('active');        
        }
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
        $('.modal .title').html('Заказать каталог');
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