jQuery(function($) {
    $('[data-id="shironekoThemeSwitcherSelector"]').change(function() {
        // Set theme and redirect to current page on theme selection.
        let searchParams = new URLSearchParams(window.location.search);
        searchParams.set('theme', $(this).val());
        window.location.search = searchParams.toString();
    });
});
