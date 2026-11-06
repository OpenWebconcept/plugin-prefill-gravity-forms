<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\GravityForms;

use OWC\PrefillGravityForms\Services\SupplierMigrationService;
use OWC\PrefillGravityForms\Traits\Logger;
use Throwable;

/**
 * Handles the migration of forms and blocks when the supplier in the add-on settings is changed from a V1
 * supplier to a V2 supplier. The user is asked for confirmation before saving the settings.
 *
 * @since NEXT
 */
class SupplierMigrationSettings
{
    use Logger;

    public const SETTING_SUPPLIER = 'owc-iconnect-supplier';
    public const SETTING_CONFIRMED = 'owc-iconnect-migrate-supplier';
    private const INPUT_PREFIX = '_gform_setting_';
    private const SCRIPT_HANDLE = 'owc-pg-supplier-migration';

    private SupplierMigrationService $service;

    public function __construct(?SupplierMigrationService $service = null)
    {
        $this->service = $service ?? new SupplierMigrationService();
    }

    /**
     * Migrates the forms and blocks when the supplier changed and the user confirmed the migration.
     * Without confirmation the user is informed about the forms and blocks that are still linked to the V1 supplier.
     *
     * Returns a HTML message which is shown with the settings saved message.
     */
    public function handleSupplierChange(string $from, string $to, bool $confirmed): string
    {
        if ($from === $to) {
            return '';
        }

        $migration = $this->service->findMigrationForGlobalSupplierChange($from, $to);

        if (null === $migration) {
            return '';
        }

        try {
            $report = $this->service->migrate($migration[0], $migration[1], ! $confirmed);
        } catch (Throwable $e) {
            $this->logException(new \Exception(sprintf('Supplier migration failed: %s', $e->getMessage()), 500, $e));

            return $this->paragraph(sprintf(
                esc_html__('Het omzetten van de formulieren en blokken naar %s is mislukt: %s', 'prefill-gravity-forms'),
                esc_html($to),
                esc_html($e->getMessage())
            ));
        }

        return $confirmed ? $this->formatMigrated($report) : $this->formatNotMigrated($report);
    }

    /**
     * Enqueues the script which asks for confirmation when the supplier is changed from the current V1 supplier
     * to a V2 supplier, see resources/js/admin/supplier-migration.js.
     */
    public function enqueueConfirmScript(string $currentSupplier): void
    {
        $from = $this->service->supplierByLabel($currentSupplier, $this->service->sourceSuppliers());
        $targets = $this->service->targetSuppliers();

        if (null === $from || ! count($targets)) {
            return;
        }

        try {
            // The forms and blocks linked to the V1 supplier are the same for every target.
            $report = $this->service->migrate($from, $targets[0], true);
        } catch (Throwable $e) {
            return;
        }

        $forms = count($report['forms']);
        $posts = count($report['posts']);

        if (0 === $forms + $posts) {
            return;
        }

        $assetPath = PG_ROOT_PATH . '/build/supplier-migration.asset.php';
        $asset = file_exists($assetPath) ? require $assetPath : ['dependencies' => [], 'version' => PG_VERSION];

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            plugins_url('build/supplier-migration.js', PG_ROOT_PATH . '/prefill-gravity-forms.php'),
            $asset['dependencies'],
            $asset['version'],
            true
        );

        $message = sprintf(
            // The %s placeholder (escaped as %%s) is replaced by the selected V2 supplier in JavaScript.
            __("Er zijn %1\$d formulier(en) en %2\$d pagina('s) met persoonsgegevens gekoppeld aan %3\$s.\n\nWil je deze automatisch omzetten naar %%s? De gekoppelde velden worden dan vervangen door de V2-velden. Velden zonder V2-tegenhanger worden na het opslaan getoond.\n\nOK: omzetten en instellingen opslaan.\nAnnuleren: alleen de instellingen opslaan.", 'prefill-gravity-forms'),
            $forms,
            $posts,
            $currentSupplier
        );

        wp_add_inline_script(
            self::SCRIPT_HANDLE,
            sprintf('window.owcPrefillSupplierMigration = %s;', wp_json_encode([
                'supplierInput' => self::INPUT_PREFIX . self::SETTING_SUPPLIER,
                'confirmedInput' => self::INPUT_PREFIX . self::SETTING_CONFIRMED,
                'targets' => array_map($this->service->supplierLabel(...), $targets),
                'message' => $message,
            ])),
            'before'
        );
    }

    private function formatMigrated(array $report): string
    {
        $from = $this->service->supplierLabel($report['from']);
        $to = $this->service->supplierLabel($report['to']);

        $parts = array_filter([
            count($report['forms']) ? sprintf(
                esc_html__('%1$d formulier(en) met %2$d gekoppelde velden', 'prefill-gravity-forms'),
                count($report['forms']),
                array_sum(array_column($report['forms'], 'migrated'))
            ) : '',
            count($report['posts']) ? sprintf(
                esc_html__('%1$d pagina(\'s) met %2$d persoonsgegevens-blokken', 'prefill-gravity-forms'),
                count($report['posts']),
                array_sum(array_column($report['posts'], 'migrated'))
            ) : '',
        ]);

        $html = count($parts) ? $this->paragraph(sprintf(
            esc_html__('%1$s zijn omgezet van %2$s naar %3$s.', 'prefill-gravity-forms'),
            implode(esc_html__(' en ', 'prefill-gravity-forms'), $parts),
            esc_html($from),
            esc_html($to)
        )) : '';

        return $html . $this->formatUnmapped($report) . $this->formatSkipped($report);
    }

    private function formatNotMigrated(array $report): string
    {
        $forms = count($report['forms']) + count($report['skipped_forms']);
        $posts = count($report['posts']);

        if (0 === $forms + $posts) {
            return '';
        }

        return $this->paragraph(sprintf(
            esc_html__('Let op: %1$d formulier(en) en %2$d pagina(\'s) met persoonsgegevens zijn nog gekoppeld aan %3$s en zijn niet omgezet. Pas deze handmatig aan of gebruik het WP-CLI commando %4$s.', 'prefill-gravity-forms'),
            $forms,
            $posts,
            esc_html($this->service->supplierLabel($report['from'])),
            sprintf('<code>wp prefill migrate-supplier %s %s</code>', esc_html($report['from']), esc_html($report['to']))
        ));
    }

    private function formatUnmapped(array $report): string
    {
        $items = [];

        foreach ($report['forms'] as $formId => $form) {
            foreach ($form['unmapped'] as $fieldId => $field) {
                $items[] = sprintf(
                    '%s &ndash; %s <code>%s</code>',
                    $this->formLink((int) $formId, $form['title']),
                    esc_html('' !== $field['label'] ? $field['label'] : sprintf(__('Veld %d', 'prefill-gravity-forms'), $fieldId)),
                    esc_html($field['value'])
                );
            }
        }

        if (! count($items)) {
            return '';
        }

        return $this->paragraph(esc_html__('De volgende velden hebben geen tegenhanger in V2 en moeten handmatig opnieuw gekoppeld worden:', 'prefill-gravity-forms'))
            . $this->list($items);
    }

    private function formatSkipped(array $report): string
    {
        $items = [];

        foreach ($report['skipped_forms'] as $formId => $form) {
            $items[] = sprintf('%s &ndash; %s', $this->formLink((int) $formId, $form['title']), esc_html($form['reason']));
        }

        if (! count($items)) {
            return '';
        }

        return $this->paragraph(esc_html__('De volgende formulieren zijn overgeslagen en moeten handmatig aangepast worden:', 'prefill-gravity-forms'))
            . $this->list($items);
    }

    private function formLink(int $formId, string $title): string
    {
        return sprintf(
            '<a href="%s">%s (ID %d)</a>',
            esc_url(admin_url(sprintf('admin.php?page=gf_edit_forms&id=%d', $formId))),
            esc_html($title),
            $formId
        );
    }

    private function paragraph(string $html): string
    {
        return sprintf('<p>%s</p>', $html);
    }

    private function list(array $items): string
    {
        return sprintf('<ul style="list-style: disc; margin-left: 1.5em;"><li>%s</li></ul>', implode('</li><li>', $items));
    }
}
