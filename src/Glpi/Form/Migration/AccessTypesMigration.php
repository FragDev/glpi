<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2025 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
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

use Glpi\DBAL\JsonFieldInterface;
use Glpi\DBAL\QueryExpression;
use Glpi\Form\AccessControl\ControlType\AllowList;
use Glpi\Form\AccessControl\ControlType\AllowListConfig;
use Glpi\Form\AccessControl\ControlType\DirectAccess;
use Glpi\Form\AccessControl\ControlType\DirectAccessConfig;
use Glpi\Form\AccessControl\FormAccessControlManager;
use Glpi\Form\Form;
use LogicException;

final class AccessTypesMigration
{
    public const PUBLIC_ACCESS_TYPE = 0;
    public const PRIVATE_ACCESS_TYPE = 1;
    public const RESTRICTED_ACCESS_TYPE = 2;

    private MigrationManager $migrationManager;
    private FormMigrationResult $result;

    public function __construct(MigrationManager $migrationManager, FormMigrationResult $result)
    {
        $this->migrationManager = $migrationManager;
        $this->result = $result;
    }

    public function getStrategyForAccessTypes(): array
    {
        return [
            self::PUBLIC_ACCESS_TYPE => DirectAccess::class,
            self::PRIVATE_ACCESS_TYPE => DirectAccess::class,
            self::RESTRICTED_ACCESS_TYPE => AllowList::class
        ];
    }

    public function getStrategyConfigForAccessTypes(array $form_access_rights): JsonFieldInterface
    {
        $clean_ids = fn($ids) => array_unique(array_filter($ids, fn($id) => is_int($id)));

        if (in_array($form_access_rights['access_rights'], [self::PUBLIC_ACCESS_TYPE, self::PRIVATE_ACCESS_TYPE])) {
            return new DirectAccessConfig(
                allow_unauthenticated: $form_access_rights['access_rights'] === self::PUBLIC_ACCESS_TYPE
            );
        } elseif ($form_access_rights['access_rights'] === self::RESTRICTED_ACCESS_TYPE) {
            return new AllowListConfig(
                user_ids: $clean_ids(json_decode($form_access_rights['user_ids'], true) ?? []),
                group_ids: $clean_ids(json_decode($form_access_rights['group_ids'], true) ?? []),
                profile_ids: $clean_ids(json_decode($form_access_rights['profile_ids'], true) ?? [])
            );
        }

        throw new LogicException("Strategy config not found for access type {$form_access_rights['access_rights']}");
    }

    /**
     * Process migration of form access types
     *
     * @return void
     */
    public function processMigrationOfFormAccessTypes(): void
    {
        // Retrieve data from glpi_plugin_formcreator_forms table
        $raw_form_access_rights = $this->migrationManager->getDB()->request([
            'SELECT' => [
                'access_rights',
                new QueryExpression('glpi_plugin_formcreator_forms.id', 'forms_id'),
                'name', // Added to get form name for status reporting
                new QueryExpression('JSON_ARRAYAGG(users_id)', 'user_ids'),
                new QueryExpression('JSON_ARRAYAGG(groups_id)', 'group_ids'),
                new QueryExpression('JSON_ARRAYAGG(profiles_id)', 'profile_ids')
            ],
            'FROM'   => 'glpi_plugin_formcreator_forms',
            'LEFT JOIN'   => [
                'glpi_plugin_formcreator_forms_users' => [
                    'ON' => [
                        'glpi_plugin_formcreator_forms_users' => 'plugin_formcreator_forms_id',
                        'glpi_plugin_formcreator_forms'       => 'id'
                    ]
                ],
                'glpi_plugin_formcreator_forms_groups' => [
                    'ON' => [
                        'glpi_plugin_formcreator_forms_groups' => 'plugin_formcreator_forms_id',
                        'glpi_plugin_formcreator_forms'        => 'id'
                    ]
                ],
                'glpi_plugin_formcreator_forms_profiles' => [
                    'ON' => [
                        'glpi_plugin_formcreator_forms_profiles' => 'plugin_formcreator_forms_id',
                        'glpi_plugin_formcreator_forms'          => 'id'
                    ]
                ]
            ],
            'GROUPBY' => ['forms_id', 'access_rights']
        ]);

        foreach ($raw_form_access_rights as $form_access_rights) {
            try {
                $form = new Form();
                if (!$form->getFromDB($this->migrationManager->getKeyMap('glpi_plugin_formcreator_forms', $form_access_rights['forms_id']))) {
                    throw new LogicException("Form with id {$form_access_rights['forms_id']} not found");
                }

                $strategy_class = self::getStrategyForAccessTypes()[$form_access_rights['access_rights']] ?? null;
                if ($strategy_class === null) {
                    throw new LogicException("Strategy class not found for access type {$form_access_rights['access_rights']}");
                }

                $manager = FormAccessControlManager::getInstance();
                $manager->createMissingAccessControlsForForm($form);

                foreach ($form->getAccessControls() as $access_control) {
                    if (!($access_control->getStrategy() instanceof $strategy_class)) {
                        continue;
                    }

                    $access_control->update([
                        'id'        => $access_control->getID(),
                        '_config'   => self::getStrategyConfigForAccessTypes($form_access_rights)->jsonSerialize(),
                        'is_active' => true,
                    ]);
                }

                // Add success status for this form
                $this->result->addAccessTypeStatus(
                    $form_access_rights['name'],
                    FormMigrationResult::ACCESS_STATUS_SUCCESS
                );
            } catch (\Exception $e) {
                // Add failure status for this form with error details
                $this->result->addAccessTypeStatus(
                    $form_access_rights['name'],
                    FormMigrationResult::ACCESS_STATUS_FAILED,
                    $e->getMessage()
                );
            }
        }
    }
}
