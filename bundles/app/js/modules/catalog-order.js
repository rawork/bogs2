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