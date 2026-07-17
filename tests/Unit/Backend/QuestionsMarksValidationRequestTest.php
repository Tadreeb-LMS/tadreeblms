<?php

namespace Tests\Unit\Backend;

use App\Http\Requests\Admin\StoreQuestionsRequest;
use App\Http\Requests\Admin\UpdateQuestionsRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class QuestionsMarksValidationRequestTest extends TestCase
{
    public function test_store_request_rejects_score_above_100(): void
    {
        $request = new StoreQuestionsRequest();
        $validator = Validator::make(
            ['question' => 'Sample question', 'score' => 150],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertSame('Marks cannot exceed 100.', $validator->errors()->first('score'));
    }

    public function test_store_request_accepts_score_at_the_100_boundary(): void
    {
        $request = new StoreQuestionsRequest();
        $validator = Validator::make(
            ['question' => 'Sample question', 'score' => 100],
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_update_request_rejects_score_above_100(): void
    {
        $request = new UpdateQuestionsRequest();
        $validator = Validator::make(
            ['question' => 'Sample question', 'score' => 101],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertSame('Marks cannot exceed 100.', $validator->errors()->first('score'));
    }

    public function test_update_request_accepts_score_at_the_100_boundary(): void
    {
        $request = new UpdateQuestionsRequest();
        $validator = Validator::make(
            ['question' => 'Sample question', 'score' => 100],
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }
}
