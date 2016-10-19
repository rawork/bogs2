'use strict';
(function($) {

    var warningMessage = $('.warning');

    //====== Initial Cart Values ======//
    var shouldSelectRegion = false,
        subTotal = 0, // products total
        shippingTotal = 0; // shipping total

    //====== Export setter ======//
    function setShoudSelectRegion(val){
        shouldSelectRegion = val;
    }
    module.exports = setShoudSelectRegion;

    //====== Modules ======//
    var autocomplete = require('./modules/autocomplete'),
        updateTotals = require('./modules/update-totals'),
        setRegion = require('./modules/set-region'),
        convert = require('./modules/price-converter'),
        mobMenu = require('./modules/mob-menu'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order'),
        modal = require('./modules/modal');

    // Close modal
    modal.closeModal();

    //====== Get subTotal ======//
    $('.product-in-cart__price').each(function(){
        subTotal += convert.priceToNum( $(this) );
    });
    

    //====== Radio ======//
    // Initial state
    var radios = $('.shipping-list input[type="radio"]'),
        activeRadioParent; // store active item
    // Set active on load
    radios.each(function(){
        var radio = $(this);
        if ( radio.prop( 'checked' ) ) {
            shippingTotal = parseInt( radio.attr('data-shipping-price') );
            activeRadioParent = radio.closest('.shipping-payment__list__item');
            activeRadioParent.addClass('active');
        }
    });
    // On Change
    var hiddenMethod = null;
    radios.on('change', function(){
        activeRadioParent.removeClass('active');
        var radio = $(this),
            hidePayment = radio.attr('data-hide-payment'),
            radioParent = radio.closest('.shipping-payment__list__item');
        shippingTotal = parseInt( radio.attr('data-shipping-price') );
        if ( radio.attr('data-select-region') === 'true' ){
            shouldSelectRegion = true;
            shippingTotal = 0;
        } else {
            shouldSelectRegion = false;
        }
        radioParent.addClass('active');
        activeRadioParent = radioParent;
        $('.select-region__field').val('').removeClass('selected'); // Clean Region Search
        updateTotals(shippingTotal, subTotal);

        // Hide/Show payment method
        if( hidePayment ){
            hiddenMethod = $(hidePayment);
            hiddenMethod.find('input[type="radio"]').prop( 'checked', false );
            hiddenMethod.hide();
        }
        if( !hidePayment && hiddenMethod) {
            hiddenMethod.show();
        }
    });

    // Update on load
    updateTotals(shippingTotal, subTotal);


    //====== Remove Item from Cart ======//
    // Check if Cart is empty
    function checkCart(){
        if( $('.product-in-cart').length === 0 ) {
            $('.products-list').html('<tr class="empty-cart"><td>Ваша корзина пуста</td></tr>')
        }
    };

    $('.remove').on('click', function(e){
        e.preventDefault();
        var id = parseInt($(this).attr('data-id'));
        var product = $(this).closest('.product-in-cart'),
            price = convert.priceToNum( product.find('.product-in-cart__price') );
        $.post('/api/basket/remove', {id: id}, function(data){
            if(data.status){
                $('#cart').html(data.minicart);
                product.fadeOut(300, function(){
                    product.remove();
                    subTotal = subTotal - price;
                    $('.sub-total__val').text( convert.numToPrice( subTotal ) );
                    checkCart();
                    updateTotals(shippingTotal, subTotal);
                });
            } else {
                console.log('remove sku from cart error');
            }
        }, 'json');

    });

    $(document).on("change", '.amount', function () {
        console.log('cart amount change');
    });

    $(document).on("click", '.amount-dec', function () {
        console.log('cart amount-dec click');
    });

    $(document).on("click", '.amount-inc', function () {
        console.log('cart amount-inc click');
    });

    //====== Autocomlete ======//
    // Click Autocomplete Item
    $(document).on('click', '.autocomplete__item', function(){
        var item = $(this),
            subjName = item.find('.autocomplete__item__name').text(),
            autocompleteList = item.parent(),
            field = autocompleteList.siblings('.select-region__field');
        shippingTotal = parseInt( item.find('.autocomplete__item__price').text() );
        setRegion(field, autocompleteList, subjName);
        shouldSelectRegion = false;
        updateTotals(shippingTotal, subTotal);
    });

    // Remove Selected Region
    $(document).on('keyup', '.select-region__field.selected', function(e){
        if( e.which <= 90 && e.which >= 48 || e.which == 8 ){
            $(this).removeClass('selected');
            shippingTotal = 0;
            updateTotals(shippingTotal, subTotal);
        }
    });

    //====== Send Order ======//
    function showMessage(text) {
        warningMessage.text(text);
        warningMessage.fadeIn();
        console.warn(text);
    }

    $('.btn-order').on('click', function(e){
        e.preventDefault();
        var paymentChecked = false,
            productsLength = $('.product-in-cart').length;
        $('input[name="payment-type"]').each(function() {
            if( $(this).prop( 'checked' ) ){
                paymentChecked = true;
            }
        });
        if (!productsLength) {
            showMessage('Корзина пуста(');
            return;
        }
        if ( !paymentChecked ) {
            showMessage('Выберите метод оплаты');
            return;
        }
        if ( shouldSelectRegion ) {
            showMessage('Выберите регион');
            return;
        }
        console.info('Заказ отправлен!');
        window.location = window.location.origin + '/order';
    });

})(jQuery);