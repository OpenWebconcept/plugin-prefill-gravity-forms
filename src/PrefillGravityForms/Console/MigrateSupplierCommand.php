<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Console;

use OWC\PrefillGravityForms\Services\SupplierMigrationService;
use Throwable;
use WP_CLI;

/**
 * @since NEXT
 */
class MigrateSupplierCommand
{
    /**
     * Migrates forms and personal data blocks from a V1 supplier to a V2 supplier.
     *
     * Switches the supplier in the form settings, maps the linked fields to the V2 options and updates the supplier of the personal data blocks.
     * Fields without a V2 counterpart are reported and unlinked, the original value is kept in 'linkedFieldValueLegacy'.
     *
     * ## OPTIONS
     *
     * <from>
     * : The V1 supplier to migrate from.
     * ---
     * options:
     *   - enable-u
     *   - pink-roccade
     *   - vrij-brp
     * ---
     *
     * <to>
     * : The V2 supplier to migrate to.
     * ---
     * options:
     *   - enable-u-v2
     *   - pink-roccade-v2
     * ---
     *
     * [--dry-run]
     * : Only report what would be migrated.
     *
     * [--yes]
     * : Do not ask for confirmation.
     *
     * ## EXAMPLES
     *
     *     wp prefill migrate-supplier enable-u enable-u-v2 --dry-run
     *     wp prefill migrate-supplier vrij-brp enable-u-v2 --yes
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        $from = (string) ($args[0] ?? '');
        $to = (string) ($args[1] ?? '');
        $dryRun = (bool) \WP_CLI\Utils\get_flag_value($assocArgs, 'dry-run', false);
        $service = new SupplierMigrationService();

        if (! $service->canMigrate($from, $to)) {
            WP_CLI::error(sprintf('There is no migration available from supplier "%s" to "%s".', $from, $to));
        }

        try {
            $report = $service->migrate($from, $to, true);
        } catch (Throwable $e) {
            WP_CLI::error($e->getMessage());

            return; // Unreachable: WP_CLI::error() exits; keeps static analysis happy.
        }

        $this->printReport($report);

        if (0 === count($report['forms']) + count($report['posts'])) {
            $this->warnSkippedForms($report);
            WP_CLI::success(sprintf('Nothing to migrate for supplier "%s".', $from));

            return;
        }

        if ($dryRun) {
            WP_CLI::success('Dry run, nothing has been changed.');

            return;
        }

        WP_CLI::confirm(sprintf('Migrate the forms and blocks above from "%s" to "%s"?', $report['from'], $report['to']), $assocArgs);

        try {
            $report = $service->migrate($from, $to);
        } catch (Throwable $e) {
            WP_CLI::error($e->getMessage());
        }

        WP_CLI::success(sprintf(
            'Migrated %d form(s) and %d post(s) from "%s" to "%s".',
            count($report['forms']),
            count($report['posts']),
            $report['from'],
            $report['to']
        ));

        $this->warnSkippedForms($report);
    }

    private function warnSkippedForms(array $report): void
    {
        if (count($report['skipped_forms'])) {
            WP_CLI::warning(sprintf('%d form(s) have been skipped, these need to be migrated manually.', count($report['skipped_forms'])));
        }
    }

    private function printReport(array $report): void
    {
        $items = [];

        foreach ($report['forms'] as $id => $form) {
            $items[] = [
                'type' => 'form',
                'id' => $id,
                'title' => $form['title'],
                'migrated' => $form['migrated'],
                'unmapped' => count($form['unmapped']),
                'note' => '',
            ];
        }

        foreach ($report['skipped_forms'] as $id => $form) {
            $items[] = ['type' => 'form', 'id' => $id, 'title' => $form['title'], 'migrated' => 0, 'unmapped' => 0, 'note' => 'skipped: ' . $form['reason']];
        }

        foreach ($report['posts'] as $id => $post) {
            $items[] = ['type' => 'post', 'id' => $id, 'title' => $post['title'], 'migrated' => $post['migrated'], 'unmapped' => 0, 'note' => ''];
        }

        if (count($items)) {
            \WP_CLI\Utils\format_items('table', $items, ['type', 'id', 'title', 'migrated', 'unmapped', 'note']);
        }

        $unmapped = [];

        foreach ($report['forms'] as $id => $form) {
            foreach ($form['unmapped'] as $fieldId => $field) {
                $unmapped[] = ['form' => $id, 'field' => $fieldId, 'label' => $field['label'], 'value' => $field['value']];
            }
        }

        if (count($unmapped)) {
            WP_CLI::log('');
            WP_CLI::log('Fields without a V2 counterpart, these need to be linked manually:');
            \WP_CLI\Utils\format_items('table', $unmapped, ['form', 'field', 'label', 'value']);
        }
    }
}
