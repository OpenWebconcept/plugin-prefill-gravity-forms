<?php
/**
 * jQuery is used to determine if a field should display custom settings.
 */
?>
<script type='text/javascript'>
    /**
     * Adds the custom prefill setting class to all field types.
     * This class is necessary for displaying the custom prefill settings element in the editor.
     */
    function addPrefillSettingToFields() {
        jQuery.each(fieldSettings, function(index, value) {
            fieldSettings[index] += ", .owc_pg_prefill_setting";
        });
    }

    /**
     * Adds the specific custom setting class for the custom fields.
     * This ensures that the unique settings for the custom fields are displayed in the editor.
     */
    function addAgeCheckSettingToFields() {
        fieldSettings['owc_pg_age_check'] += ', .owc_pg_age_check_setting';
		fieldSettings['owc_pg_municipality_check'] += ', .owc_pg_municipality_check_setting';
    }

    /**
     * Populates the custom settings fields with their saved values when a field's settings are loaded.
     * This is particularly focused on the 'owc_pg_age_check' field type.
     *
     * @param {object} field The field whose settings are being loaded.
     */
    function populateCustomSettings(field) {
        jQuery("#linkedField").val(field["linkedFieldValue"]);

        if (field.type === 'owc_pg_age_check') {
            jQuery('#pgAgeCheckMinimumAge').val(field['pgAgeCheckMinimumAgeValue'] || '');
            jQuery('#pgAgeCheckFailed').val(field['pgAgeCheckFailedValue'] || '');
            jQuery('#pgAgeCheckSuccess').val(field['pgAgeCheckSuccessValue'] || '');
        }

		if (field.type === 'owc_pg_municipality_check') {
			jQuery('#pgMunicipalityCodeCheck').val(field['pgMunicipalityCodeCheckValue'] || '');
			jQuery('#pgMunicipalityCheckFailed').val(field['pgMunicipalityCheckFailedMessage'] || '');
            jQuery('#pgMunicipalityCheckSuccess').val(field['pgMunicipalityCheckSuccessMessage'] || '');
		}
    }

    // Execute the functions when the document is ready
    jQuery(document).ready(function() {
        addPrefillSettingToFields();
        addAgeCheckSettingToFields();

        // Bind the populate function to the event when field settings are loaded
        jQuery(document).bind("gform_load_field_settings", function(event, field, form) {
            populateCustomSettings(field);
        });
    });
</script>
