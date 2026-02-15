<script type="text/javascript">
(function() {
    function waitForjQuery(callback) {
        if (typeof jQuery !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForjQuery(callback); }, 50);
        }
    }
    
    waitForjQuery(function() {
        jQuery(document).on('gform_post_render', function(){
            jQuery('.owc_prefilled input').attr('readonly','readonly');
        });
    });
})();
</script>