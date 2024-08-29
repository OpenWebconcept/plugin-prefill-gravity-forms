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

        $minimumAgeSetting = $this->get_minimun_age_setting();

        if (! $this->check_age($minimumAgeSetting, $dateOfBirth)) {
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
        $minimumAgeSetting = $this->get_minimun_age_setting();

        if (empty($bsn) || empty($minimumAgeSetting) || empty($dateOfBirth)) {
            $message = __('Log in met uw DigiD, zonder BSN-nummer kan de leeftijdscheck niet uitgevoerd worden.', 'prefill-gravity-forms');

            if (empty($minimumAgeSetting)) {
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

        if (! $this->check_age($minimumAgeSetting, $dateOfBirth)) {
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

    // # End overwriting parent methods --------------------------------------------------------------------------------------------------

    /**
     * Retrieves the setting from this field which is used for the field validation.
     */
    protected function get_minimun_age_setting(): int
    {
        $setting = $this->pgAgeCheckMinimumAgeValue ?? false;

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
        $message = __("Dit is een voorbeeldweergave van het veld 'OWC leeftijdscheck'. Stel de minimale leeftijd en de gewenste validatie berichten in via de instellingen van dit veld onder de kop 'OWC leeftijdscheck'.", 'prefill-gravity-forms');
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

    protected function check_age(int $minimumAgeSetting, string $dateOfBirth): bool
    {
        $now = new DateTime('', new DateTimeZone(wp_timezone_string()));

        try {
            $dateOfBirth = new DateTime($dateOfBirth, new DateTimeZone(wp_timezone_string()));
        } catch(Exception $e) {
            return false;
        }

        return $now->diff($dateOfBirth)->y >= $minimumAgeSetting;
    }
}
