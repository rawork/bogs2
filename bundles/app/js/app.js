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
    $(document).on('click', '.add-product', function(e) {
        e.preventDefault();
        var that = $(this);
        var id = $('#sizes option:selected').val();
        var amount = $('.amount').val();

        $.post("/api/basket/edit", {id: id, amount: amount},
            function(data){
                $('#cart').html(data.minicart);
                that.html('В корзине');
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
        }
    });

    $(document).on("click", ".amount-dec", function () {
        var amount = parseInt($(this).siblings(".amount").val());
        if (isNaN(amount)) {
            $(this).siblings(".amount").val(0);
            return false;
        }

        if (amount > 0) {
            $(this).siblings(".amount").val(--amount);
        }
    });

    $(document).on("click", ".amount-inc", function () {
        var amount = parseInt($(this).siblings(".amount").val());

        if (isNaN(amount)) {
            $(this).siblings(".amount").val(0);
            return false;
        }

        ++amount;

        var max = parseInt($(this).siblings(".amount").attr("data-value"));
        if (amount > max) {
            $(this).siblings(".amount").val(max);
        } else {
            $(this).siblings(".amount").val(amount);
        }
    });

    $(document).on('change', '#sizes', function(e){
        $('.amount')
            .attr('data-value', $('#sizes option:selected').attr('data-value'))
            .val(1);
        $('.add-product').html('Добавить в корзину');
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
