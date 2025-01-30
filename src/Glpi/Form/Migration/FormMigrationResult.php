<?php

namespace Glpi\Form\Migration;

class FormMigrationResult
{
    private bool $success = true;
    private array $errors = [];
    private array $warnings = [];
    private array $info = [];

    // Stockage des statuts de migration par formulaire
    private array $forms_status = [];

    public const STATUS_SUCCESS = 'success';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_FAILED = 'failed';

    private array $skipped_questions = [];

    public function addError(string $message): void
    {
        $this->errors[] = $message;
        $this->success = false;
    }

    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function addInfo(string $message): void
    {
        $this->info[] = $message;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function addFormStatus(string $form_name, string $status, ?string $details = null): void
    {
        $this->forms_status[] = [
            'name' => $form_name,
            'status' => $status,
            'details' => $details
        ];
    }

    public function getFormsStatus(): array
    {
        return $this->forms_status;
    }

    public function getFormStatusSummary(): array
    {
        $summary = [
            self::STATUS_SUCCESS => 0,
            self::STATUS_PARTIAL => 0,
            self::STATUS_FAILED => 0,
        ];

        foreach ($this->forms_status as $status) {
            $summary[$status['status']]++;
        }

        return $summary;
    }

    public function addSkippedQuestion(string $form_name, string $question_name, string $type, string $reason): void
    {
        if (!isset($this->skipped_questions[$form_name])) {
            $this->skipped_questions[$form_name] = [];
        }
        $this->skipped_questions[$form_name][] = [
            'name' => $question_name,
            'type' => $type,
            'reason' => $reason
        ];
    }

    public function getSkippedQuestions(): array
    {
        return $this->skipped_questions;
    }
}
