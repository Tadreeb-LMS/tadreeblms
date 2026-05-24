<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class QuestionCreationLocalizationTest extends TestCase
{
    public function test_question_creation_option_builder_uses_translation_keys(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/test_questions/create.blade.php'
        );

        $this->assertStringContainsString("__('labels.backend.questions.options')", $view);
        $this->assertStringContainsString("__('labels.backend.questions.add_option')", $view);
        $this->assertStringContainsString("questionOptionLabels.removeOption", $view);
        $this->assertStringContainsString("questionOptionLabels.optionRequired", $view);

        $this->assertStringNotContainsString('Opzioni', $view);
        $this->assertStringNotContainsString('Aggiungi Opzione', $view);
        $this->assertStringNotContainsString('Rimuovi', $view);
        $this->assertStringNotContainsString('Corretta', $view);
        $this->assertStringNotContainsString('Nessuna opzione aggiunta', $view);
    }

    public function test_english_question_option_labels_are_defined(): void
    {
        $labels = include __DIR__ . '/../../../resources/lang/en/labels.php';
        $questions = $labels['backend']['questions'];

        $this->assertSame('Options', $questions['options']);
        $this->assertSame('Add Option', $questions['add_option']);
        $this->assertSame('Remove', $questions['remove_option']);
        $this->assertSame('Correct', $questions['fields']['correct']);
        $this->assertSame('Please fill the option field before adding it.', $questions['option_required']);
    }
}
