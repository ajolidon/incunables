require('../css/app.scss');

const $ = require('jquery');
window.$ = window.jQuery = $;
require('bootstrap');


$(document).ready(function() {
    $('.clickable-url').off().click(function(){
        let url = $(this).data('url');
        document.location.href = url;
    });

    $('.clickable-url-new-window').off().click(function(){
        let url = $(this).data('url');
        console.log('new window');
        window.open(url);
    });


});

$(window).scroll(function(){
    let top = $('.header').height();
    let navbar = $('.navbar-main');
    if($(this).scrollTop()>=top){
        navbar.addClass('fixed-top');
    }else{
        navbar.removeClass('fixed-top');
    }
});