<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Services;

use GFAPI;
use function OWC\PrefillGravityForms\Foundation\Helpers\config;

use stdClass;

/**
 * Migrates forms and 'personal-data-row' blocks from a V1 supplier to a V2 supplier, e.g. VrijBRP to EnableU V2.
 *
 * Forms: the form setting 'owc-form-setting-supplier' is switched and the 'linkedFieldValue' of every field is
 * mapped to the V2 option. The original values are kept in 'owc-form-setting-supplier-legacy' and 'linkedFieldValueLegacy'.
 * Blocks: the 'selectedSupplier' and, when needed, the 'selectedOption' attributes are updated.
 *
 * Only forms and blocks linked to the V1 supplier are touched, so running a migration twice is harmless.
 *
 * @since NEXT
 */
class SupplierMigrationService
{
    public const FORM_SETTING_SUPPLIER = 'owc-form-setting-supplier';
    public const FORM_SETTING_SUPPLIER_LEGACY = 'owc-form-setting-supplier-legacy';
    public const FORM_SETTING_THEME_MAPPING = 'owc-iconnect-theme-mapping-options-file';
    public const FIELD_LEGACY_PROPERTY = 'linkedFieldValueLegacy';
    public const BLOCK_NAME = 'owc-prefill-gravity-forms/personal-data-row';

    /**
     * Returns the keys of the V1 suppliers that can be migrated from, e.g. 'enable-u'.
     */
    public function sourceSuppliers(): array
    {
        return array_keys($this->sources());
    }

    /**
     * Returns the keys of the V2 suppliers that can be migrated to, e.g. 'enable-u-v2'.
     */
    public function targetSuppliers(): array
    {
        return array_keys($this->targets());
    }

    public function canMigrate(string $from, string $to): bool
    {
        return isset($this->sources()[$from], $this->targets()[$to]);
    }

    /**
     * Find the migration for a change of the global supplier setting, which uses the controller names, e.g.
     * 'VrijBRP' to 'EnableUV2'. Returns the supplier keys, e.g. ['vrij-brp', 'enable-u-v2'], or null when there is none.
     *
     * @return array{0: string, 1: string}|null
     */
    public function findMigrationForGlobalSupplierChange(string $from, string $to): ?array
    {
        $from = $this->supplierByLabel($from, $this->sourceSuppliers());
        $to = $this->supplierByLabel($to, $this->targetSuppliers());

        return null === $from || null === $to ? null : [$from, $to];
    }

    /**
     * Returns the key of a supplier by its controller name, e.g. 'EnableU' becomes 'enable-u'.
     */
    public function supplierByLabel(string $label, ?array $suppliers = null): ?string
    {
        foreach ($suppliers ?? array_keys((array) config('suppliers.mapping', [])) as $supplier) {
            if ('' !== $label && $this->supplierLabel($supplier) === $label) {
                return $supplier;
            }
        }

        return null;
    }

    /**
     * Returns the controller name of a supplier key, e.g. 'enable-u' becomes 'EnableU'.
     */
    public function supplierLabel(string $supplier): string
    {
        $mapping = config('suppliers.mapping', []);

        return is_array($mapping) ? (string) ($mapping[$supplier] ?? '') : '';
    }

    /**
     * Migrate (or with $dryRun only report) all forms and blocks linked to the V1 supplier $from to the V2 supplier $to.
     *
     * @return array{
     *     from: string,
     *     to: string,
     *     forms: array<int, array{title: string, migrated: int, unmapped: array<int, array{label: string, value: string}>}>,
     *     skipped_forms: array<int, array{title: string, reason: string}>,
     *     posts: array<int, array{title: string, migrated: int}>
     * }
     */
    public function migrate(string $from, string $to, bool $dryRun = false): array
    {
        if (! $this->canMigrate($from, $to)) {
            throw new \InvalidArgumentException(sprintf('There is no migration available from supplier "%s" to "%s".', $from, $to));
        }

        return [
            'from' => $from,
            'to' => $to,
            ...$this->migrateForms($from, $to, $dryRun),
            'posts' => $this->migrateBlocks($from, $to, $dryRun),
        ];
    }

    private function sources(): array
    {
        $sources = config('supplier-migrations.sources', []);

        return is_array($sources) ? $sources : [];
    }

    private function targets(): array
    {
        $targets = config('supplier-migrations.targets', []);

        return is_array($targets) ? $targets : [];
    }

    private function migrateForms(string $supplier, string $to, bool $dryRun): array
    {
        $report = ['forms' => [], 'skipped_forms' => []];

        if (! class_exists('GFAPI')) {
            return $report;
        }

        // Include inactive and trashed forms, these could be restored later on.
        foreach (GFAPI::get_forms(null, null) as $form) {
            if (($form[self::FORM_SETTING_SUPPLIER] ?? '') !== $supplier) {
                continue;
            }

            $formId = (int) $form['id'];
            $title = (string) ($form['title'] ?? '');

            // Theme mapping files contain custom options which can not be mapped automatically.
            $themeMapping = (string) ($form[self::FORM_SETTING_THEME_MAPPING] ?? '0');

            if ('0' !== $themeMapping && '' !== $themeMapping) {
                $report['skipped_forms'][$formId] = [
                    'title' => $title,
                    'reason' => sprintf(__('gebruikt mappingbestand "%s" uit het thema', 'prefill-gravity-forms'), basename($themeMapping)),
                ];

                continue;
            }

            $result = $this->migrateFormFields($form, $this->sources()[$supplier], ! $dryRun);
            $report['forms'][$formId] = ['title' => $title] + $result;

            if ($dryRun) {
                continue;
            }

            $form[self::FORM_SETTING_SUPPLIER_LEGACY] = $supplier;
            $form[self::FORM_SETTING_SUPPLIER] = $to;

            $updated = GFAPI::update_form($form);

            if (is_wp_error($updated)) {
                unset($report['forms'][$formId]);
                $report['skipped_forms'][$formId] = ['title' => $title, 'reason' => $updated->get_error_message()];
            }
        }

        return $report;
    }

    /**
     * Maps the linked field values of the form fields, the fields are objects so the form is modified by reference.
     * When $apply is false the fields are left untouched, so a dry run does not alter forms cached by Gravity Forms.
     */
    private function migrateFormFields(array $form, array $fieldMapping, bool $apply): array
    {
        $result = ['migrated' => 0, 'unmapped' => []];

        foreach ($form['fields'] ?? [] as $field) {
            $value = $field->linkedFieldValue ?? '';

            if (! is_string($value) || '' === $value) {
                continue;
            }

            $newValue = $fieldMapping[$value] ?? null;

            if (! is_string($newValue) || '' === $newValue) {
                // Keep the original value so it is clear what the field was linked to.
                $result['unmapped'][(int) $field->id] = [
                    'label' => (string) ($field->label ?? ''),
                    'value' => $value,
                ];

                continue;
            }

            $result['migrated']++;

            if (! $apply) {
                continue;
            }

            $field->{self::FIELD_LEGACY_PROPERTY} = $value;
            $field->linkedFieldValue = $newValue;
        }

        return $result;
    }

    private function migrateBlocks(string $supplier, string $target, bool $dryRun): array
    {
        global $wpdb;

        $from = $this->supplierLabel($supplier);
        $to = $this->supplierLabel($target);

        if ('' === $from || '' === $to) {
            return [];
        }

        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type != 'revision' AND post_content LIKE %s",
            '%' . $wpdb->esc_like('<!-- wp:' . self::BLOCK_NAME . ' ') . '%'
        ));

        $report = [];

        foreach ($posts ?: [] as $post) {
            $migrated = 0;
            $content = $this->migrateBlocksInContent(
                (string) $post->post_content,
                $from,
                ['value' => $to, 'label' => $this->targets()[$target]['block_supplier_label'] ?? $to],
                $migrated
            );

            if (0 === $migrated) {
                continue;
            }

            $report[(int) $post->ID] = ['title' => (string) $post->post_title, 'migrated' => $migrated];

            if ($dryRun) {
                continue;
            }

            /**
             * Update the database directly, wp_update_post() would create a revision, change the modified date and
             * could filter the content (kses) when the current user is not allowed to use unfiltered HTML.
             */
            $wpdb->update($wpdb->posts, ['post_content' => $content], ['ID' => (int) $post->ID]);
            clean_post_cache((int) $post->ID);
        }

        return $report;
    }

    /**
     * Rewrites the attributes in the block comment delimiters of the 'personal-data-row' blocks.
     * Only the delimiters are touched, the rest of the content stays exactly the same.
     */
    public function migrateBlocksInContent(string $content, string $from, array $to, int &$migrated = 0): string
    {
        $pattern = sprintf('/<!--\s+wp:%s\s+(\{.*?\})\s+(\/)?-->/s', preg_quote(self::BLOCK_NAME, '/'));
        $optionMapping = config('supplier-migrations.block_options', []);

        return (string) preg_replace_callback($pattern, function (array $matches) use ($from, $to, $optionMapping, &$migrated): string {
            // Decode as objects to preserve empty objects when encoding again.
            $attributes = json_decode($matches[1]);

            if (! $attributes instanceof stdClass) {
                return $matches[0];
            }

            $supplier = $attributes->selectedSupplier->value ?? '';
            $option = $attributes->selectedOption->value ?? '';
            $mappedOption = is_string($option) ? ($optionMapping[$option] ?? null) : null;

            // Blocks already linked to the V2 supplier only need their option mapped, e.g. after an earlier migration.
            if ($supplier !== $from && ($supplier !== $to['value'] || ! is_array($mappedOption))) {
                return $matches[0];
            }

            $attributes->selectedSupplier = (object) $to;

            if (is_array($mappedOption)) {
                $attributes->selectedOption = (object) $mappedOption;
            }

            $migrated++;

            return sprintf(
                '<!-- wp:%s %s %s-->',
                self::BLOCK_NAME,
                serialize_block_attributes((array) $attributes),
                empty($matches[2]) ? '' : '/'
            );
        }, $content);
    }
}
