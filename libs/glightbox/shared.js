
$( document ).ready(function() {
    const glightbox = GLightbox({
        selector: ".glightbox-galerie a",
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });

    glightbox.on('open', () => {
        // Do something
    });
});