<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms;

use GF_Field;
use WP_HTML_Tag_Processor;

/**
 * Makes prefilled fields read-only server-side.
 *
 * Previously this was done with an inline script on 'gform_post_render',
 * which is blocked by a nonce-based Content Security Policy.
 *
 * @since 2.1.3
 */
class GravityFormsPrefilledFields
{
    public const CSS_CLASS = 'owc_prefilled';

    public function addReadonlyAttribute(string $content, GF_Field $field, $value, $entryID, $formID): string
    {
        // Don't use is_admin(): AJAX submissions run through admin-ajax.php and re-render the form there.
        if (\GFCommon::is_form_editor() || \GFCommon::is_entry_detail() || ! $this->isPrefilled($field)) {
            return $content;
        }

        $processor = new WP_HTML_Tag_Processor($content);

        while ($processor->next_tag('input')) {
            $processor->set_attribute('readonly', 'readonly');
        }

        return $processor->get_updated_html();
    }

    protected function isPrefilled(GF_Field $field): bool
    {
        $classes = preg_split('/\s+/', (string) $field->cssClass) ?: [];

        return in_array(self::CSS_CLASS, $classes, true);
    }
}
