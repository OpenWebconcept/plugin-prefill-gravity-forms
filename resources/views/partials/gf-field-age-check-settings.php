<li class="owc_pg_age_check_setting field_setting">
    <label for="pgAgeCheckMinimumAge" class="section_label">
        <?php _e('Minimale leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<small><?php _e('Minimale leeftijd die de ingelogde burger dient te hebben.', 'prefill-gravity-forms'); ?></small>
	<input id="pgAgeCheckMinimumAge" type="text" class="form-control" name="pgAgeCheckMinimumAge" value="" onchange="SetFieldProperty('pgAgeCheckMinimumAgeValue', this.value);">
</li>
<li class="owc_pg_age_check_setting field_setting">
    <label for="pgAgeCheckFailed" class="section_label">
        <?php _e('Bericht bij onjuiste leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgAgeCheckFailed" type="text" class="form-control" name="pgAgeCheckFailed" value="" onchange="SetFieldProperty('pgAgeCheckFailedMessage', this.value);">
</li>
<li class="owc_pg_age_check_setting field_setting">
	<label for="pgAgeCheckSuccess" class="section_label">
        <?php _e('Bericht bij juiste leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgAgeCheckSuccess" type="text" class="form-control" name="pgAgeCheckSuccess" value="" onchange="SetFieldProperty('pgAgeCheckSuccessMessage', this.value);">
</li>
