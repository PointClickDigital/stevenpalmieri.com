// var $carouselElem = find(".de-carousel-slider-parent")
// $carouselElem.find(".de-carousel-slider-parent .de-carousel-slider-childs").each(function (index, slide) {
$(document).ready( function() {
    // DOM ELEMENTS
    $('.de-carousel-slider-parent').each(function(index, value) {
        console.log(`.de-carousel-slider-parent${index}: ${this.id}`);
    });
    $('.de-carousel-slider-parent').click(function(index, value) {
        // var parent = $('.de-carousel-slider-parent').index();
        console.log(`.de-carousel-slider-parent${index}: ${this.id}`);
        // var parent = $('.de-carousel-slider-parent').index();
        // alert ('Parentz: '+parent);
    })

    $('.de-carousel-slider-parent .de-carousel-slider-childs').click(function() {
        $('.dethemekit-carousel-inner').slick('slickGoTo',$(this).index());
        $('.de-carousel-slider-parent .de-carousel-slider-childs').removeClass('de-carousel-active');
        $('.de-carousel-slider-parent .de-carousel-slider-childs').eq($(this).index()).addClass('de-carousel-active');
    //     // alert (eq($(this).index()));
    //     // var index = $('.de-carousel-slider-parent .de-carousel-slider-childs').index(this);
        
        var parent = $('.de-carousel-slider-parent').index();
        var child = $('.de-carousel-slider-parent .de-carousel-slider-childs').index(this);
        // alert ('Parent: '+parent+' Child: '+child);
    })
    $('.de-carousel-slider-parent .de-carousel-slider-childs.de-carousel-slider-childs').click(function() {
        $('.dethemekit-carousel-inner').slick('slickGoTo',$(this).index());
        $('.de-carousel-slider-parent .de-carousel-slider-childs').removeClass('de-carousel-active');
        $('.de-carousel-slider-parent .de-carousel-slider-childs').eq($(this).index()).addClass('de-carousel-active');
    })
    // $('.angka').click(function() {
    //     var diklik = $(this).index();
    //     alert(diklik);
    // })
    $('li.a').click(function() {
        var indexe = $(this).index();
        // $( "span" ).text( "That was div index #" + index );
        alert (indexe);
    })

})