
<?php
/**
 * Script for select element
 **/
?>
<script type='text/javascript'>
    // To display custom field under each type of Gravity Forms field
    jQuery.each(fieldSettings, function(index, value) {
        fieldSettings[index] += ", .highlight_setting";
    });
    jQuery(document).bind("gform_load_field_settings", function(event, field, form) {
        jQuery("#linkedField").val(field["linkedFieldValue"]);
    });
</script>