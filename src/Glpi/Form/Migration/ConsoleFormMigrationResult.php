<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2025 Teclib' and contributors.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace Glpi\Form\Migration;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleFormMigrationResult extends FormMigrationResult
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function addError(string $message): void
    {
        parent::addError($message);
        $this->output->writeln('<error>' . $message . '</error>');
    }

    public function addWarning(string $message): void
    {
        parent::addWarning($message);
        $this->output->writeln('<comment>' . $message . '</comment>');
    }

    public function addInfo(string $message): void
    {
        parent::addInfo($message);
        $this->output->writeln('<info>' . $message . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }

    public function addFormStatus(string $form_name, string $status, ?string $details = null): void
    {
        parent::addFormStatus($form_name, $status, $details);

        $status_symbols = [
            self::STATUS_SUCCESS => '✓',
            self::STATUS_PARTIAL => '~',
            self::STATUS_FAILED  => '✗',
        ];

        $status_styles = [
            self::STATUS_SUCCESS => 'info',
            self::STATUS_PARTIAL => 'comment',
            self::STATUS_FAILED  => 'error',
        ];

        $message = sprintf(
            '%s [%s] %s',
            $status_symbols[$status],
            $form_name,
            $details ? "($details)" : ''
        );

        $this->output->writeln(
            sprintf('<%s>%s</%s>', $status_styles[$status], $message, $status_styles[$status])
        );
    }

    public function addAccessTypeStatus(string $form_name, string $status, ?string $details = null): void
    {
        parent::addAccessTypeStatus($form_name, $status, $details);

        $status_symbols = [
            self::ACCESS_STATUS_SUCCESS => '✓',
            self::ACCESS_STATUS_FAILED  => '✗',
        ];

        $status_styles = [
            self::ACCESS_STATUS_SUCCESS => 'info',
            self::ACCESS_STATUS_FAILED  => 'error',
        ];

        $message = sprintf(
            '%s [%s] Access Type migration %s',
            $status_symbols[$status],
            $form_name,
            $details ? "($details)" : ''
        );

        $this->output->writeln(
            sprintf('<%s>%s</%s>', $status_styles[$status], $message, $status_styles[$status])
        );
    }

    public function displayMigrationSummary(): void
    {
        $this->output->writeln("\nMigration Summary:");

        $table = new Table($this->output);
        $table->setHeaders(['Form', 'Questions', 'Access control', 'Issues', 'Details']);

        $forms_status = $this->getFormsStatus();
        $access_types_status = array_column($this->getAccessTypesStatus(), null, 'name');
        $skipped_questions = $this->getSkippedQuestions();

        $form_status_decorators = [
            self::STATUS_SUCCESS => ['symbol' => '✓', 'style' => 'info'],
            self::STATUS_PARTIAL => ['symbol' => '~', 'style' => 'comment'],
            self::STATUS_FAILED  => ['symbol' => '✗', 'style' => 'error'],
        ];

        $access_status_decorators = [
            self::ACCESS_STATUS_SUCCESS => ['symbol' => '✓', 'style' => 'info'],
            self::ACCESS_STATUS_FAILED  => ['symbol' => '✗', 'style' => 'error'],
        ];

        foreach ($forms_status as $form) {
            $form_name = $form['name'];
            $form_decorator = $form_status_decorators[$form['status']];

            // Get access status if exists
            $access_status = $access_types_status[$form_name] ?? null;
            $access_decorator = $access_status ? $access_status_decorators[$access_status['status']] : null;

            // Count skipped questions
            $issues_count = isset($skipped_questions[$form_name]) ? count($skipped_questions[$form_name]) : 0;
            $issues_text = sprintf(
                '<%s>%d issue(s)</%s>',
                $issues_count > 0 ? 'error' : 'info',
                $issues_count,
                $issues_count > 0 ? 'error' : 'info'
            );

            // Create main row
            $table->addRow([
                $form_name,
                sprintf(
                    '<%s>%s %s</%s>',
                    $form_decorator['style'],
                    $form_decorator['symbol'],
                    ucfirst($form['status']),
                    $form_decorator['style']
                ),
                $access_decorator ? sprintf(
                    '<%s>%s %s</%s>',
                    $access_decorator['style'],
                    $access_decorator['symbol'],
                    ucfirst($access_status['status']),
                    $access_decorator['style']
                ) : '',
                $issues_text,
                $form['details'] ? sprintf('<comment>%s</comment>', $form['details']) : ''
            ]);

            // Add skipped questions as sub-rows if any
            if (isset($skipped_questions[$form_name])) {
                foreach ($skipped_questions[$form_name] as $q) {
                    $table->addRow([
                        '',
                        '',
                        '',
                        '',
                        sprintf(
                            '<error>↳ [%s] %s (%s)</error>',
                            $q['type'],
                            $q['name'],
                            $q['reason']
                        )
                    ]);
                }
            }

            // Add access error details if any
            if ($access_status && $access_status['details']) {
                $table->addRow([
                    '',
                    '',
                    '',
                    '',
                    sprintf('<error>↳ Access: %s</error>', $access_status['details'])
                ]);
            }
        }

        // Add summary totals at the bottom
        $form_summary = $this->getFormStatusSummary();
        $access_summary = $this->getAccessTypesStatusSummary();
        $table->addRow(new TableSeparator());

        $total_issues = array_reduce(
            $skipped_questions,
            fn ($carry, $questions) => $carry + count($questions),
            0
        );

        $table->addRow([
            'Totals',
            sprintf(
                '<info>✓:%d</info> <comment>~:%d</comment> <error>✗:%d</error>',
                $form_summary[self::STATUS_SUCCESS],
                $form_summary[self::STATUS_PARTIAL],
                $form_summary[self::STATUS_FAILED]
            ),
            sprintf(
                '<info>✓:%d</info> <error>✗:%d</error>',
                $access_summary[self::ACCESS_STATUS_SUCCESS],
                $access_summary[self::ACCESS_STATUS_FAILED]
            ),
            $total_issues > 0
                ? sprintf('<error>Issues: %d</error>', $total_issues)
                : sprintf('<info>Issues: %d</info>', $total_issues),
            ''
        ]);

        $table->render();
    }
}
