(function($) {
    'use strict';

    var win = $(window),
        body = $('body'),
        winHeight = win.outerHeight(),
        modalDialog = $('.modal-dialog');


    $(document).on('click', '.scrollto', function(e){
        e.preventDefault();
        var el = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(el).offset().top
        }, 1000);
    });

    $(document).on('click', '.modal .close, .modal-overlay', function(e){
        body.removeClass('no-scroll');
        e.preventDefault();
        $(this).parents('.modal').addClass('hidden');
    });

   function getMarginTop(){
        var modalDialogHeight = modalDialog.outerHeight(),
        marginTop = ( modalDialogHeight + 50 ) < winHeight ? ( winHeight - modalDialogHeight ) / 2 : 50;
        $('.modal-dialog').css({
            marginTop: marginTop    
        });
    }

    function showModal(){
        $('.modal').removeClass('hidden');
        body.addClass('no-scroll');
        if ($('.modal .gallery-img-big img').length) { // Wait when big image loaded
            $('.modal .gallery-img-big img').load(getMarginTop);
        } else {
            getMarginTop();
        }
    }

    // Call order
    $(document).on('click', '.show-call-order', function(e){
        e.preventDefault();
        $('.modal .title').html('Заказать звонок');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="phone" placeholder="Ваш телефон"><button class="call-order">Заказать</button></form>');
        showModal();
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

    $(document).on('click', '.show-catalog-order', function(e){
        e.preventDefault();
        $('.modal .title').html('Заказать каталог');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="email" placeholder="Ваш e-mail"><input type="text" id="phone" placeholder="Ваш телефон"><button class="catalog-order">Заказать</button></form>');
        showModal();
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

    $(document).on('click', '.show-product-order', function(e){
        e.preventDefault();
        $.post("/ajax/form", {},
            function(data){
                $('.modal .title').html('Оформить заказ');
                $('.modal .content').html(data.text);
                showModal();
                yaCounter29093585.reachGoal('show_order_product');
            }, "json");

    });

    $(document).on('click', '.product-order', function(e){
        e.preventDefault();

        var products = [],
            emptySize = false;
        $('.size-input').each(function(index){
            if ($(this).val() === '') {
                $(this).css('background-color', 'rgba(255,100,100,0.9)');
                emptySize = true;
            }
            products.push({id: $(this).attr('data-id'), 'size': $(this).val()});
        });

        var name = $('input#name').val();
        var email = $('input#email').val();
        var phone = $('input#phone').val();
        var address = '-';
//            var address = $('input#address').val();

        if (emptySize || '' == name  || '' == phone || '' == email ) { //|| '' == address
            if ('' == name) {
                $('input#name').css('background-color', 'rgba(255,100,100,0.9)');
            }
            if ('' == email) {
                $('input#email').css('background-color', 'rgba(255,100,100,0.9)');
            }
            if ('' == phone) {
                $('input#phone').css('background-color', 'rgba(255,100,100,0.9)');
            }
            return;
        }

        $.post("/ajax/order", {products: products, name: name, email: email, phone: phone, address: address},
            function(data){
                $('.modal .content').html(data.text);
                $('.cart').hide();
                $('.add-product').removeClass('active').html('Выбрать');
                yaCounter29093585.reachGoal('order_product');
            }, "json");
    });

    $(document).on('click', '.add-product', function(e) {
        e.preventDefault();
        var that = $(this);
        var id = that.attr('data-id');
        that.toggleClass('active');
        if (that.hasClass('active')) {
            $.post("/ajax/add", {id: id, amount: 1},
                function(data){
                    $('#cart').html(data.text);
                    $('.cart').show();
                    that.html('В корзине');
                    $('body').removeClass('no-scroll');
                    $('.modal').addClass('hidden');
                    yaCounter29093585.reachGoal('add_product');
                }, "json");
        } else {
            $.post("/ajax/add", {id: id, amount: -1},
                function(data){
                    if (data.text) {
                        $('#cart').html(data.text);
                        $('.cart').show();
                    } else {
                        $('body').removeClass('no-scroll');
                        $('.cart').hide();
                    }
                    that.html('Выбрать');
                }, "json");

        }
    });

    $(document).on('click', 'a.remove', function(e) {
        e.preventDefault();
        var that = $(this);
        var id = that.attr('data-id');

        $.post("/ajax/add", {id: id, amount: -1},
            function(data){
                $('#product_line_'+id).remove();
                $('.add-product[data-id='+id+']').removeClass('active').html('В корзину');
                if (data.text) {
                    $('#cart').html(data.text);
                    $('.cart').show();
                } else {
                    $('.modal').addClass('hidden');
                    body.removeClass('no-scroll');
                    $('.cart').hide();
                }

            }, "json");
    });

    $(document).on('click', '.social a', function(e){
        e.preventDefault();
        window.open($(this).attr('href'), 'Share BOGS-SHOP.RU', 'width=600,height=400,menubar=no,location=yes,resizable=no,scrollbars=yes,status=no');
    });

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
    $('.view-product').on('click', function(e){
        e.preventDefault();
        var title = $(this).siblings('.name').text();
        $.ajax({
            url: '/bundles/public/modal-product.html'
        }).done(function(data){
            $('.modal .title').html(title);
            $('.modal .content').html(data); // Dummy data
            showModal();
        });
    })

})(jQuery);

$(document).ready(function(){
    'use strict';
    $.post("/ajax/cart", {},
        function(data){
            if (data.text) {
                $('#cart').html(data.text);
                $('.cart').show();
            } else {
                $('.cart').hide();
            }
        }, "json");

    //====== Mobile menu ======//
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
        var url = $(this).attr('data-img-big');
        $('.gallery-img-big img').attr('src', url);
    })

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

});
