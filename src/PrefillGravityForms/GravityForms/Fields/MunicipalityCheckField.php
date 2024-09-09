<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms\Fields;

use GF_Field;
use OWC\PrefillGravityForms\GravityForms\Fields\Traits\CheckBSN;
use OWC\PrefillGravityForms\GravityForms\Fields\Traits\Icons;

class MunicipalityCheckField extends GF_Field
{
    use CheckBSN;
    use Icons;

    protected const MUNICIPALITY_CHECK_VALIDATION_FAILED_CSS_CLASS = 'error';
    protected const MUNICIPALITY_CHECK_VALIDATION_SUCCESS_CSS_CLASS = 'success';

    public $type = 'owc_pg_municipality_check';

    public function get_form_editor_field_title()
    {
        return esc_attr__('OWC Gemeentecheck', 'prefill-gravity-forms');
    }

    public function get_form_editor_field_description()
    {
        return esc_attr__('Op basis van de gemeentecode uit de BRP, verkregen wanneer een burger inlogt met DigiD, wordt er gekeken of de burger in de juiste gemeente woont. De gemeentecode waarop gecontroleerd wordt is instelbaar.', 'prefill-gravity-forms');
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
            'owc_pg_municipality_check_setting',
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
        $municipalityCode = $value;

        if (empty($municipalityCode)) {
            $this->failed_validation = true;
            $this->validation_message = __('Het ophalen van de gemeentecode van uw gemeente is mislukt, probeer het later nog eens.', 'prefill-gravity-forms');
        }

        $municipalityCodeSetting = $this->pgMunicipalityCodeCheckValue ?? false;

        if (! $this->check_municipality_code($municipalityCodeSetting, $municipalityCode)) {
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
        $municipalityCode = $value;
        $municipalityCodeSetting = $this->get_municipality_code_setting();

        if (empty($bsn) || empty($municipalityCodeSetting)) {
            $message = __('Log in met uw DigiD, zonder BSN-nummer kan de gemeentecheck niet uitgevoerd worden.', 'prefill-gravity-forms');

            if (empty($municipalityCodeSetting)) {
                $message = __('Dit veld is onjuist geconfigueerd, contacteer de beheerder van deze website.', 'prefill-gravity-forms');
            }

            return $this->format_field_input(
                $form,
                '',
                $message,
                $this->get_error_svg(),
                self::MUNICIPALITY_CHECK_VALIDATION_FAILED_CSS_CLASS
            );
        }

        if (! $this->check_municipality_code($municipalityCodeSetting, $municipalityCode)) {
            return $this->format_field_input(
                $form,
                $value,
                $this->get_check_failed_message(),
                $this->get_error_svg(),
                self::MUNICIPALITY_CHECK_VALIDATION_FAILED_CSS_CLASS
            );
        }

        return $this->format_field_input(
            $form,
            $value,
            $this->get_check_success_message(),
            $this->get_success_svg(),
            self::MUNICIPALITY_CHECK_VALIDATION_SUCCESS_CSS_CLASS
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
        $municipalityCode = $_POST["input_$this->id"] ?? '';
        $municipalityCodeSetting = $this->get_municipality_code_setting();

        if ($municipalityCodeSetting && $municipalityCode && $this->check_municipality_code($municipalityCodeSetting, $municipalityCode)) {
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

        $tabindex_string = '' === (rgar($atts, 'tabindex')) ?  '' : ' tabindex="' . esc_attr($atts['tabindex']) . '"';
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
    protected function get_municipality_code_setting(): string
    {
        return $this->pgMunicipalityCodeCheckValue ?? '';
    }

    /**
     * Retrieves the message that is shown when the municipality check fails.
     * When no message is configured the default message is shown.
     */
    protected function get_check_failed_message(): string
    {
        $checkFailedMessage = $this->pgMunicipalityCheckFailedMessage ?? false;

        return $checkFailedMessage ?: __('U woont niet in de juiste gemeente om dit formulier te mogen invullen.', 'prefill-gravity-forms');
    }

    /**
     * Retrieves the message that is shown when the municipality check is successful.
     * When no message is configured the default message is shown.
     */
    protected function get_check_success_message(): string
    {
        $checkSuccessMessage = $this->pgMunicipalityCheckSuccessMessage ?? false;

        return $checkSuccessMessage ?: __('U woont in de juiste gemeente om dit formulier te mogen invullen.', 'prefill-gravity-forms');
    }

    public function get_field_tab_content_template_path(): string
    {
        return 'partials/gf-field-municipality-check-settings.php';
    }

    protected function get_field_input_editor(): string
    {
        $message = __("Dit is een voorbeeldweergave van het veld 'OWC Gemeentecheck'. Stel de gemeentecode in waarop gecontroleerd dient te worden in via de instellingen van dit veld onder de kop 'OWC Gemeentecheck'.", 'prefill-gravity-forms');
        $additionalMessage = __('Vergeet niet om dit veld automatisch te laten invullen met de gemeentecode vanuit de BRP.', 'prefill-gravity-forms');

        return "<div class='ginput_container owc-pg is-editor'><p class='owc-pg-alert editor-info'>{$message}</p><p>{$additionalMessage}</p></div>";
    }

    protected function format_field_input(array $form, string $value, string $message, string $icon, string $alert_type_class): string
    {
        $field_id = $this->is_entry_detail() || $this->is_form_editor() || 0 == $form['id'] ? "input_$this->id" : 'input_' . $form['id'] . "_$this->id";
        $input = "<p class='owc-pg-alert owc-pg-municipality-check {$alert_type_class}'>$icon $message</p>";
        $input_hidden = "<input name='input_{$this->id}' id='{$field_id}' type='hidden' value='{$value}' />";

        return "<div class='ginput_container owc-pg' id='{$this->id}'>{$input}{$input_hidden}</div>";
    }

    protected function check_municipality_code(string $municipalityCodeSetting, string $municipalityCode): bool
    {
        return $municipalityCodeSetting === $municipalityCode;
    }
}
