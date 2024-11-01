$(window).load(function() {
    try {
        $('.flexslider').flexslider({
            animation: "slide",
            prevText:"",
            nextText:"",
            controlNav: false,
            slideshow: false
        });
    }
    catch (e) {
        console.log("Unable to init flexslider");
    }
});