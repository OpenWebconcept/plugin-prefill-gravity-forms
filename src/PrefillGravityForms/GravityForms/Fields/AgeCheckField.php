<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms\Fields;

use DateTime;
use DateTimeZone;
use Exception;
use GF_Field;
use OWC\PrefillGravityForms\GravityForms\Fields\Traits\CheckBSN;
use OWC\PrefillGravityForms\GravityForms\Fields\Traits\Icons;

class AgeCheckField extends GF_Field
{
    use CheckBSN;
    use Icons;

    protected const AGE_CHECK_VALIDATION_FAILED_CSS_CLASS = 'error';
    protected const AGE_CHECK_VALIDATION_SUCCESS_CSS_CLASS = 'success';

    public $type = 'owc_pg_age_check';

    public function get_form_editor_field_title()
    {
        return esc_attr__('OWC leeftijdscheck', 'prefill-gravity-forms');
    }

    public function get_form_editor_field_description()
    {
        return esc_attr__('Op basis van het BSN-nummer, verkregen wanneer een burger inlogt met DigiD, wordt de BRP bevraagd. De leeftijd van de burger wordt berekend en op basis daarvan wordt een melding weergegeven die aangeeft of de burger de juiste leeftijd heeft.', 'prefill-gravity-forms');
    }

    public function get_form_editor_field_icon()
    {
        return 'dashicons-yard-y';
    }

    public function get_form_editor_field_settings()
    {
        return [
            'conditional_logic_field_setting',
            'admin_label_setting',
            'css_class_setting',
            'description_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'rules_setting',
            'owc_pg_age_check_setting',
        ];
    }

    public function get_form_editor_button()
    {
        return [
            'group' => 'owc_pg',
            'text' => $this->get_form_editor_field_title(),
        ];
    }

    public function is_conditional_logic_supported()
    {
        return true;
    }

    public function validate($value, $form)
    {
        $dateOfBirth = $value;

        if (empty($dateOfBirth)) {
            $this->failed_validation = true;
            $this->validation_message = __('Het ophalen van uw geboortedatum is mislukt, probeer het later nog eens.', 'prefill-gravity-forms');
        }

        if (! $this->check_age($dateOfBirth)) {
            $this->failed_validation = true;
            $this->validation_message = $this->get_check_failed_message();
        }
    }

    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead)
    {
        if (rgblank($value)) {
            return '';
        }

        return ! empty($value) ? __('Geslaagd', 'prefill-gravity-forms') : __('Mislukt', 'prefill-gravity-forms');
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        if ($this->is_form_editor()) {
            return $this->get_field_input_editor();
        }

        $bsn = $this->check_bsn_value_from_session();
        $dateOfBirth = $value;
        $minimumAgeSetting = $this->get_minimum_age_setting();
        $maximumAgeSetting = $this->get_maximum_age_setting();

        if (empty($bsn) || empty($dateOfBirth || (empty($minimumAgeSetting) && empty($maximumAgeSetting)))) {
            $message = __('Log in met uw DigiD, zonder BSN-nummer kan de leeftijdscheck niet uitgevoerd worden.', 'prefill-gravity-forms');

            if (empty($minimumAgeSetting) && empty($maximumAgeSetting)) {
                $message = __('Dit veld is onjuist geconfigueerd, contacteer de beheerder van deze website.', 'prefill-gravity-forms');
            }

            return $this->format_field_input(
                $form,
                '',
                $message,
                $this->get_error_svg(),
                self::AGE_CHECK_VALIDATION_FAILED_CSS_CLASS
            );
        }

        if (! $this->check_age($dateOfBirth)) {
            return $this->format_field_input(
                $form,
                $value,
                $this->get_check_failed_message(),
                $this->get_error_svg(),
                self::AGE_CHECK_VALIDATION_FAILED_CSS_CLASS
            );
        }

        return $this->format_field_input(
            $form,
            $value,
            $this->get_check_success_message(),
            $this->get_success_svg(),
            self::AGE_CHECK_VALIDATION_SUCCESS_CSS_CLASS
        );
    }

    /**
     * Returns the HTML markup for the field's containing element.
     *
     * @since 2.5
     *
     * @param array $atts Container attributes.
     * @param array $form The current Form object.
     *
     * @return string
     */
    public function get_field_container($atts, $form)
    {
        $dateOfBirth = $_POST["input_$this->id"] ?? $this->defaultValue;
        $successMessageIsEmpty = empty($this->pgAgeCheckSuccessMessage ?? false);

        if ($dateOfBirth && $this->check_age($dateOfBirth) && $successMessageIsEmpty) {
            $atts = [
                'style' => 'display:none',
            ];
        }

        // Get the field container tag.
        $tag = $this->get_field_container_tag($form);

        // Parse the provided attributes.
        $atts = wp_parse_args($atts, [
            'id' => '',
            'class' => '',
            'style' => '',
            'tabindex' => '',
            'aria-atomic' => '',
            'aria-live' => '',
            'data-field-class' => '',
            'data-field-position' => '',
        ]);

        $tabindex_string = '' === (rgar($atts, 'tabindex')) ? '' : ' tabindex="' . esc_attr($atts['tabindex']) . '"';
        $disable_ajax_reload = $this->disable_ajax_reload();
        $ajax_reload_id = 'skip' === $disable_ajax_reload || 'true' === $disable_ajax_reload || true === $disable_ajax_reload ? 'true' : esc_attr(rgar($atts, 'id'));
        $is_form_editor = $this->is_form_editor();

        $target_input_id = esc_attr(rgar($atts, 'id'));

        // Get the field sidebar messages, this could be an array of messages or a warning message string.
        $field_sidebar_messages = \GFCommon::is_form_editor() ? $this->get_field_sidebar_messages() : '';
        $sidebar_message_type = 'warning';
        $sidebar_message_content = $field_sidebar_messages;

        if (is_array($field_sidebar_messages)) {
            $sidebar_message = is_array(rgar($field_sidebar_messages, '0')) ? array_shift($field_sidebar_messages) : $field_sidebar_messages;
            $sidebar_message_type = rgar($sidebar_message, 'type');
            $sidebar_message_content = rgar($sidebar_message, 'content');
        }

        if (! empty($sidebar_message_content)) {
            $atts['class'] .= ' gfield_' . ('error' === $sidebar_message_type ? 'warning' : $sidebar_message_type);
            if ('error' === $sidebar_message_type) {
                $atts['aria-invalid'] = 'true';
            }
        }

        return sprintf(
            '<%1$s id="%2$s" class="%3$s" %4$s%5$s%6$s%7$s%8$s%9$s data-js-reload="%10$s" %11$s>%12$s{FIELD_CONTENT}</%1$s>',
            $tag,
            esc_attr(rgar($atts, 'id')),
            esc_attr(rgar($atts, 'class')),
            rgar($atts, 'style') ? ' style="' . esc_attr($atts['style']) . '"' : '',
            false === (rgar($atts, 'tabindex')) ? '' : $tabindex_string,
            rgar($atts, 'aria-atomic') ? ' aria-atomic="' . esc_attr($atts['aria-atomic']) . '"' : '',
            rgar($atts, 'aria-live') ? ' aria-live="' . esc_attr($atts['aria-live']) . '"' : '',
            rgar($atts, 'data-field-class') ? ' data-field-class="' . esc_attr($atts['data-field-class']) . '"' : '',
            rgar($atts, 'data-field-position') ? ' data-field-position="' . esc_attr($atts['data-field-position']) . '"' : '',
            $ajax_reload_id,
            rgar($atts, 'aria-invalid') ? ' aria-invalid="true"' : '',
            empty($sidebar_message_content) ? '' : '<span class="field-' . $sidebar_message_type . '-message-content hidden">' . \GFCommon::maybe_wp_kses($sidebar_message_content) . '</span>'
        );

    }

    // # End overwriting parent methods --------------------------------------------------------------------------------------------------

    /**
     * Retrieves the setting from this field which is used for the field validation.
     */
    protected function get_minimum_age_setting(): int
    {
        $setting = $this->pgAgeCheckMinimumAgeValue ?? false;

        return is_numeric($setting) ? (int) $setting : 0;
    }

    protected function get_maximum_age_setting(): int
    {
        $setting = $this->pgAgeCheckMaximumAgeValue ?? false;

        return is_numeric($setting) ? (int) $setting : 0;
    }

    /**
     * Retrieves the message that is shown when the age check fails.
     * When no message is configured the default message is shown.
     */
    protected function get_check_failed_message(): string
    {
        $checkFailedMessage = $this->pgAgeCheckFailedMessage ?? false;

        return $checkFailedMessage ?: __('U heeft niet de juiste leeftijd om dit formulier te mogen invullen.', 'prefill-gravity-forms');
    }

    /**
     * Retrieves the message that is shown when the age check is successful.
     * When no message is configured the default message is shown.
     */
    protected function get_check_success_message(): string
    {
        $checkSuccessMessage = $this->pgAgeCheckSuccessMessage ?? false;

        return $checkSuccessMessage ?: __('U heeft de juiste leeftijd om dit formulier te mogen invullen.', 'prefill-gravity-forms');
    }

    public function get_field_tab_content_template_path(): string
    {
        return 'partials/gf-field-age-check-settings.php';
    }

    protected function get_field_input_editor(): string
    {
        $message = __("Dit is een voorbeeldweergave van het veld 'OWC leeftijdscheck'. Stel de minimale en/of maximale leeftijd en de gewenste validatie berichten in via de instellingen van dit veld onder de kop 'OWC leeftijdscheck'.", 'prefill-gravity-forms');
        $additionalMessage = __('Vergeet niet om dit veld automatisch te laten invullen met de geboortedatum vanuit de BRP.', 'prefill-gravity-forms');

        return "<div class='ginput_container owc-pg is-editor'><p class='owc-pg-alert editor-info'>{$message}</p><p>{$additionalMessage}</p></div>";
    }

    protected function format_field_input(array $form, string $value, string $message, string $icon, string $alert_type_class): string
    {
        $field_id = $this->is_entry_detail() || $this->is_form_editor() || 0 == $form['id'] ? "input_$this->id" : 'input_' . $form['id'] . "_$this->id";
        $input = "<p class='owc-pg-alert owc-pg-age-check {$alert_type_class}'>$icon $message</p>";
        $input_hidden = "<input name='input_{$this->id}' id='{$field_id}' type='hidden' value='{$value}' />";

        return "<div class='ginput_container owc-pg' id='{$this->id}'>{$input}{$input_hidden}</div>";
    }

    protected function check_age(string $dateOfBirth): bool
    {
        $minimumAgeSetting = $this->get_minimum_age_setting();
        $maximumAgeSetting = $this->get_maximum_age_setting();

        if (0 === $minimumAgeSetting && 0 === $maximumAgeSetting) {
            return true;
        }

        $now = new DateTime('', new DateTimeZone(wp_timezone_string()));

        try {
            $dateOfBirth = new DateTime($dateOfBirth, new DateTimeZone(wp_timezone_string()));
        } catch (Exception $e) {
            return false;
        }

        $age = $now->diff($dateOfBirth)->y;
        if (0 !== $minimumAgeSetting && $age < $minimumAgeSetting) {
            return false;
        }
        if (0 !== $maximumAgeSetting && $age > $maximumAgeSetting) {
            return false;
        }

        return true;
    }
}
