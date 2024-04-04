<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
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

namespace Glpi\Form;

use CommonDBTM;
use Entity;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Form\Destination\FormDestination;
use Html;
use Glpi\DBAL\QuerySubQuery;
use Glpi\Form\QuestionType\QuestionTypesManager;
use Log;
use Override;
use Session;

/**
 * Helpdesk form
 */
final class Form extends CommonDBTM
{
    public static $rightname = 'form';

    public $dohistory = true;

    public $history_blacklist = [
        'date_mod',
    ];

    /**
     * Lazy loaded array of sections
     * Should always be accessed through getSections()
     * @var Section[]|null
     */
    protected ?array $sections = null;

    #[Override]
    public static function getTypeName($nb = 0)
    {
        return _n('Form', 'Forms', $nb);
    }

    #[Override]
    public static function getIcon()
    {
        return "ti ti-forms";
    }

    #[Override]
    public function defineTabs($options = [])
    {
        $tabs = parent::defineTabs();
        $this->addStandardTab(AnswersSet::getType(), $tabs, $options);
        $this->addStandardTab(FormDestination::getType(), $tabs, $options);
        $this->addStandardTab(Log::getType(), $tabs, $options);
        return $tabs;
    }

    #[Override]
    public function showForm($id, array $options = [])
    {
        if (!empty($id)) {
            $this->getFromDB($id);
        } else {
            $this->getEmpty();
        }
        $this->initForm($id, $options);

        // We will be editing forms from this page
        echo Html::script("js/form_editor_controller.js");

        $types_manager = QuestionTypesManager::getInstance();
        $js_files = [];
        foreach ($types_manager->getQuestionTypes() as $type) {
            foreach ($type::loadJavascriptFiles() as $file) {
                if (!in_array($file, $js_files)) {
                    $js_files[] = $file;
                    echo Html::script($file);
                }
            }
        }

        // Render twig template
        $twig = TemplateRenderer::getInstance();
        $twig->display('pages/admin/form/form_editor.html.twig', [
            'item'                   => $this,
            'params'                 => $options,
            'question_types_manager' => $types_manager,
        ]);
        return true;
    }

    #[Override]
    public function rawSearchOptions()
    {
        $search_options = parent::rawSearchOptions();

        $search_options[] = [
            'id'            => '2',
            'table'         => self::getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'massiveaction' => false,
            'datatype'      => 'number'
        ];
        $search_options[] = [
            'id'            => '80',
            'table'         => Entity::getTable(),
            'field'         => 'completename',
            'name'          => Entity::getTypeName(1),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
        ];
        $search_options[] = [
            'id'       => '3',
            'table'    => $this->getTable(),
            'field'    => 'is_active',
            'name'     => __('Active'),
            'datatype' => 'bool'
        ];
        $search_options[] = [
            'id'            => '4',
            'table'         => $this->getTable(),
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false
        ];
        $search_options[] = [
            'id'            => '5',
            'table'         => $this->getTable(),
            'field'         => 'date_creation',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false
        ];

        return $search_options;
    }

    #[Override]
    public function post_getFromDB()
    {
        // Clear lazy loaded data
        $this->clearLazyLoadedData();
    }

    #[Override]
    public function post_addItem()
    {
        // Automatically create the first form section unless specified otherwise
        if (!isset($this->input['_do_not_init_sections'])) {
            $this->createFirstSection();
        }
    }

    #[Override]
    public function prepareInputForUpdate($input)
    {
        // Insert date_mod even if the framework would handle it by itself
        // This avoid "empty" updates when the form itself is not modified but
        // its questions are
        $input['date_mod'] = $_SESSION['glpi_currenttime'];

        return $input;
    }

    #[Override]
    public function post_updateItem($history = 1)
    {
        /** @var \DBmysql $DB */
        global $DB;

        // Tests will already be running inside a transaction, we can't create
        // a new one in this case
        if ($DB->inTransaction()) {
            // Update questions and sections
            $this->updateExtraFormData();
        } else {
            $DB->beginTransaction();

            try {
                // Update questions and sections
                $this->updateExtraFormData();
                $DB->commit();
            } catch (\Throwable $e) {
                // Delete the "Item sucessfully updated" message if it exist
                Session::deleteMessageAfterRedirect(
                    $this->formatSessionMessageAfterAction(__('Item successfully updated'))
                );

                // Do not keep half updated data
                $DB->rollback();

                // Propagate exception to ensure the server return an error code
                throw $e;
            }
        }
    }

    #[Override]
    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Section::class,
                FormDestination::class,
            ]
        );
    }

    /**
     * Get sections of this form
     *
     * @return Section[]
     */
    public function getSections(): array
    {
        // Lazy loading
        if ($this->sections === null) {
            $this->sections = [];

            // Read from database
            $sections_data = (new Section())->find(
                [self::getForeignKeyField() => $this->fields['id']],
                'rank ASC',
            );

            foreach ($sections_data as $row) {
                $section = new Section();
                $section->getFromResultSet($row);
                $section->post_getFromDB();
                $this->sections[$row['id']] = $section;
            }
        }

        return $this->sections;
    }

    /**
     * Get all questions for this form
     *
     * @return Question[]
     */
    public function getQuestions(): array
    {
        $questions = [];
        foreach ($this->getSections() as $section) {
            // Its important to use the "+" operator here and not array_merge
            // because the keys must be preserved
            $questions = $questions + $section->getQuestions();
        }
        return $questions;
    }

    /**
     * Get all defined destinations of this form
     *
     * @return FormDestination[]
     */
    public function getDestinations(): array
    {
        $destinations = [];
        $destinations_data = (new FormDestination())->find(
            [self::getForeignKeyField() => $this->fields['id']],
        );

        foreach ($destinations_data as $row) {
            $destination = new FormDestination();
            $destination->getFromResultSet($row);
            $destination->post_getFromDB();
            $destinations[$row['id']] = $destination;
        }

        return $destinations;
    }

    /**
     * Update extra form data found in other tables (sections and questions)
     *
     * @return void
     */
    protected function updateExtraFormData(): void
    {
        // We must update sections first, as questions depend on them.
        // However, they must only be deleted after questions have been updated.
        // This prevents cascade deletion to delete their questions that might
        // have been moved to another section.
        $this->updateSections();
        $this->updateQuestions();
        $this->deleteMissingSections();
        $this->deleteMissingQuestions();
    }

    /**
     * Clear lazy loaded data
     *
     * @return void
     */
    protected function clearLazyLoadedData(): void
    {
        $this->sections = null;
    }

    /**
     * Create the first section of a form
     *
     * @return void
     */
    protected function createFirstSection(): void
    {
        $section = new Section();
        $section->add([
            'forms_forms_id' => $this->fields['id'],
            'name'           => __("First section"),
            'rank'           => 0,
        ]);
    }

    /**
     * Update form's sections using the special data found in
     * $this->input['_sections']
     *
     * @return void
     */
    protected function updateSections(): void
    {
        $sections = $this->input['_sections'] ?? [];

        // Keep track of sections found
        $found_sections = [];

        // Parse each submitted section
        foreach ($sections as $form_data) {
            $section = new Section();

            // Newly created section, may need to be updated using temporary UUID instead of ID
            if ($form_data['_use_uuid']) {
                $uuid = $form_data['id'];
                $form_data['id'] = $_SESSION['form_editor_sections_uuid'][$uuid] ?? 0;
            } else {
                $uuid = null;
            }

            if ($form_data['id'] == 0) {
                // Add new section
                unset($form_data['id']);
                $id = $section->add($form_data);

                if (!$id) {
                    throw new \RuntimeException("Failed to add section");
                }

                // Store temporary UUID -> ID mapping in session
                if ($uuid !== null) {
                    $_SESSION['form_editor_sections_uuid'][$uuid] = $id;
                }
            } else {
                // Update existing section
                $success = $section->update($form_data);
                if (!$success) {
                    throw new \RuntimeException("Failed to update section");
                }
                $id = $section->getID();
            }

            // Keep track of its id
            $found_sections[] = $id;
        }

        // Deletion will be handled in a separate method
        $this->input['_found_sections'] = $found_sections;

        // Special input has been handled, it can be deleted
        unset($this->input['_sections']);
    }

    /**
     * Delete sections that were not found in the submitted data
     *
     * @return void
     */
    protected function deleteMissingSections(): void
    {
        // We can't run this code if we don't have the list of updated sections
        if (!isset($this->input['_found_sections'])) {
            return;
        }
        $found_sections = $this->input['_found_sections'];

        // Safety check to avoid deleting all sections if some code run an update
        // without the _sections keys.
        // Deletion is only done if the special "_delete_missing_sections" key
        // is present
        $delete_missing_sections = $this->input['_delete_missing_sections'] ?? false;
        if ($delete_missing_sections) {
            // Avoid empty IN clause
            if (empty($found_sections)) {
                $found_sections = [-1];
            }

            $missing_sections = (new Section())->find([
                // Is part of this form
                'forms_forms_id' => $this->fields['id'],

                // Was not found in the submitted data
                'id' => ['NOT IN', $found_sections],
            ]);

            foreach ($missing_sections as $row) {
                $section = new Section();
                $success = $section->delete($row);
                if (!$success) {
                    throw new \RuntimeException("Failed to delete section");
                }
            }
        }

        unset($this->input['_found_sections']);
    }

    /**
     * Update form's questions using the special data found in
     * $this->input['_questions']
     *
     * @return void
     */
    protected function updateQuestions(): void
    {
        $questions = $this->input['_questions'] ?? [];

        // Keep track of questions found
        $found_questions = [];

        // Parse each submitted question
        foreach ($questions as $question_data) {
            $question = new Question();

            if ($question_data["_use_uuid_for_sections_id"]) {
                // This question was added to a newly created section
                // We need to find the correct section id using the temporary UUID
                $uuid = $question_data['forms_sections_id'];
                $question_data['forms_sections_id'] = $_SESSION['form_editor_sections_uuid'][$uuid] ?? 0;
            }

            // Newly created question, may need to be updated using temporary UUID instead of ID
            if ($question_data['_use_uuid']) {
                $uuid = $question_data['id'];
                $question_data['id'] = $_SESSION['form_editor_questions_uuid'][$uuid] ?? 0;
            } else {
                $uuid = null;
            }

            if ($question_data['id'] == 0) {
                // Add new question
                unset($question_data['id']);
                $id = $question->add($question_data);

                if (!$id) {
                    throw new \RuntimeException("Failed to add question");
                }

                // Store temporary UUID -> ID mapping in session
                if ($uuid !== null) {
                    $_SESSION['form_editor_questions_uuid'][$uuid] = $id;
                }
            } else {
                // Update existing section
                $success = $question->update($question_data);
                if (!$success) {
                    throw new \RuntimeException("Failed to update question");
                }
                $id = $question->getID();
            }

            // Keep track of its id
            $found_questions[] = $id;
        }

        // Deletion will be handled in a separate method
        $this->input['_found_questions'] = $found_questions;

        // Special input has been handled, it can be deleted
        unset($this->input['_questions']);
    }

    /**
     * Delete sections that were not found in the submitted data
     *
     * @return void
     */
    protected function deleteMissingQuestions(): void
    {
        // We can't run this code if we don't have the list of updated sections
        if (!isset($this->input['_found_questions'])) {
            return;
        }
        $found_questions = $this->input['_found_questions'];

        // Safety check to avoid deleting all questions if some code run an update
        // without the _questions keys.
        // Deletion is only done if the special "_delete_missing_questions" key
        // is present
        $delete_missing_questions = $this->input['_delete_missing_questions'] ?? false;
        if ($delete_missing_questions) {
            // Avoid empty IN clause
            if (empty($found_questions)) {
                $found_questions = [-1];
            }

            $missing_questions = (new Question())->find([
                // Is part of this form
                'forms_sections_id' => new QuerySubQuery([
                    'SELECT' => 'id',
                    'FROM'   => Section::getTable(),
                    'WHERE'  => [
                        'forms_forms_id' => $this->fields['id'],
                    ],
                ]),
                // Was not found in the submitted data
                'id' => ['NOT IN', $found_questions],
            ]);

            foreach ($missing_questions as $row) {
                $question = new Question();
                $success = $question->delete($row);
                if (!$success) {
                    throw new \RuntimeException("Failed to delete question");
                }
            }
        }

        unset($this->input['_found_questions']);
    }
}
