$(function(){

    /* Popup */

    var closePopup = function() {
        $('.popup.open').removeClass('open');
    };

    $('.popup .close').on('click',function(e){
        e.preventDefault();
        closePopup();
        var hash = document.URL.substr(document.URL.indexOf('#')+1) 
        setCookie('popup',hash,30);
    });

});