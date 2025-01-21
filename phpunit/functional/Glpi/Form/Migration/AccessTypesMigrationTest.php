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

namespace tests\units\Glpi\Form\Migration;

use DbTestCase;
use Glpi\Form\AccessControl\ControlType\AllowList;
use Glpi\Form\AccessControl\ControlType\AllowListConfig;
use Glpi\Form\AccessControl\ControlType\DirectAccess;
use Glpi\Form\AccessControl\ControlType\DirectAccessConfig;
use Glpi\Form\AccessControl\FormAccessControlManager;
use Glpi\Form\Form;
use Glpi\Form\Migration\MigrationManager;
use Glpi\Tests\FormTesterTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class AccessTypesMigrationTest extends DbTestCase
{
    use FormTesterTrait;

    public static function provideFormMigrationWithAccessTypes(): iterable
    {
        $access_config = new DirectAccessConfig(
            allow_unauthenticated: true
        );
        yield 'Test form migration for access types with public access' => [
            'form_name' => 'Test form migration for access types with public access',
            'active_access_control_data' => [
                Form::getForeignKeyField() => fn () => getItemByTypeName(
                    Form::class,
                    'Test form migration for access types with public access',
                    true
                ),
                'strategy'                 => DirectAccess::class ,
                'config'                   => json_encode($access_config->jsonSerialize()),
                'is_active'                => 1
            ]
        ];

        $access_config = new DirectAccessConfig(
            allow_unauthenticated: false
        );
        yield 'Test form migration for access types with private access' => [
            'form_name' => 'Test form migration for access types with private access',
            'active_access_control_data' => [
                Form::getForeignKeyField() => fn () => getItemByTypeName(
                    Form::class,
                    'Test form migration for access types with private access',
                    true
                ),
                'strategy'                 => DirectAccess::class ,
                'config'                   => json_encode($access_config->jsonSerialize()),
                'is_active'                => 1
            ]
        ];

        $access_config = new AllowListConfig(
            user_ids: [2],
            profile_ids: [4, 1],
            group_ids: []
        );
        yield 'Test form migration for access types with restricted access' => [
            'form_name' => 'Test form migration for access types with restricted access',
            'active_access_control_data' => [
                Form::getForeignKeyField() => fn () => getItemByTypeName(
                    Form::class,
                    'Test form migration for access types with restricted access',
                    true
                ),
                'strategy'                 => AllowList::class ,
                'config'                   => json_encode($access_config->jsonSerialize()),
                'is_active'                => 1
            ]
        ];
    }

    #[DataProvider('provideFormMigrationWithAccessTypes')]
    public function testFormMigrationWithAccessTypes($form_name, $active_access_control_data): void
    {
        global $DB;
        $mm = new MigrationManager($DB);
        $mm->doMigration(false);

        $form = getItemByTypeName(Form::class, $form_name);
        $active_access_controls = FormAccessControlManager::getInstance()->getActiveAccessControlsForForm($form);
        foreach ($active_access_controls as $active_access_control) {
            $expected_data = array_map(
                fn($value) => is_callable($value) ? $value() : $value,
                $active_access_control_data
            );
            $actual_data = array_intersect_key($active_access_control->fields, $active_access_control_data);

            // Decode the config JSON strings to compare them as arrays, ignoring the token field
            if (isset($expected_data['config']) && isset($actual_data['config'])) {
                $expected_config = json_decode($expected_data['config'], true);
                $actual_config = json_decode($actual_data['config'], true);
                unset($expected_config['token'], $actual_config['token']);
                $expected_data['config'] = $expected_config;
                $actual_data['config'] = $actual_config;
            }

            $this->assertEqualsCanonicalizing($expected_data, $actual_data);
        }
    }
}
