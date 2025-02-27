<li class="owc_pg_age_check_setting field_setting">
    <label for="pgAgeCheckMinimumAge" class="section_label">
        <?php esc_html_e('Minimale leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<small><?php esc_html_e('Minimale leeftijd die de ingelogde burger dient te hebben.', 'prefill-gravity-forms'); ?></small>
	<input id="pgAgeCheckMinimumAge" type="text" class="form-control" name="pgAgeCheckMinimumAge" value="" onchange="SetFieldProperty('pgAgeCheckMinimumAgeValue', this.value);">
</li>
<li class="owc_pg_age_check_setting field_setting">
    <label for="pgAgeCheckMaximumAge" class="section_label">
        <?php esc_html_e('Maximale leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<small><?php esc_html_e('Maximale leeftijd die de ingelogde burger dient te hebben.', 'prefill-gravity-forms'); ?></small>
	<input id="pgAgeCheckMaximumAge" type="text" class="form-control" name="pgAgeCheckMaximumAge" value="" onchange="SetFieldProperty('pgAgeCheckMaximumAgeValue', this.value);">
</li>
<li class="owc_pg_age_check_setting field_setting">
    <label for="pgAgeCheckFailed" class="section_label">
        <?php esc_html_e('Bericht bij onjuiste leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgAgeCheckFailed" type="text" class="form-control" name="pgAgeCheckFailed" value="" onchange="SetFieldProperty('pgAgeCheckFailedMessage', this.value);" placeholder="<?php esc_html_e('U heeft niet de juiste leeftijd om dit formulier te mogen invullen.', 'prefill-gravity-forms');?>">
</li>
<li class="owc_pg_age_check_setting field_setting">
	<label for="pgAgeCheckSuccess" class="section_label">
        <?php esc_html_e('Bericht bij juiste leeftijd', 'prefill-gravity-forms'); ?>
    </label>
	<input id="pgAgeCheckSuccess" type="text" class="form-control" name="pgAgeCheckSuccess" value="" onchange="SetFieldProperty('pgAgeCheckSuccessMessage', this.value);" placeholder="<?php esc_html_e('U heeft de juiste leeftijd om dit formulier te mogen invullen.', 'prefill-gravity-forms');?>">
</li>
