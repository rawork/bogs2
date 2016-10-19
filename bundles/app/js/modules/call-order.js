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