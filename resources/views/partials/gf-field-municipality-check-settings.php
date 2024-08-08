<li class="owc_pg_municipality_check_setting field_setting">
    <label for="pgMunicipalityCodeCheck" class="section_label">
        <?php _e('Gemeente code', 'prefill-gravity-forms'); ?>
    </label>
	<small><?php _e('De gemeente code waarop gecontroleerd dient te worden.', 'prefill-gravity-forms'); ?></small>
	<input id="pgMunicipalityCodeCheck" type="text" class="form-control" name="pgMunicipalityCodeCheck" value="" onchange="SetFieldProperty('pgMunicipalityCodeCheckValue', this.value);">
</li>
<li class="owc_pg_municipality_check_setting field_setting">
    <label for="pgMunicipalityCheckFailed" class="section_label">
        <?php _e('Bericht bij onjuiste gemeente', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgMunicipalityCheckFailed" type="text" class="form-control" name="pgMunicipalityCheckFailed" value="" onchange="SetFieldProperty('pgMunicipalityCheckFailedMessage', this.value);">
</li>
<li class="owc_pg_municipality_check_setting field_setting">
	<label for="pgMunicipalityCheckSuccess" class="section_label">
        <?php _e('Bericht bij juiste gemeente', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgMunicipalityCheckSuccess" type="text" class="form-control" name="pgMunicipalityCheckSuccess" value="" onchange="SetFieldProperty('pgMunicipalityCheckSuccessMessage', this.value);">
</li>
