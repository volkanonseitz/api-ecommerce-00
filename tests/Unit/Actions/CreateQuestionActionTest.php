<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateQuestionAction;
use App\Models\Question;
use App\Modules\Question\DTO\QuestionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateQuestionActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_question()
    {
        // Arrange
        $data = new QuestionData(
            question: 'What is the meaning of life?',
            answer: '42',
            language: 'en',
            product_id: 1, // Assuming a product_id exists
            user_id: 1, // Assuming a user_id exists
            shop_id: 1 // Assuming a shop_id exists
        );

        // Mock the Question model to prevent actual database interaction during unit test
        // and assert that the create method is called with the correct data
        $questionMock = $this->mock(Question::class);
        $questionMock->shouldReceive('create')
            ->once()
            ->with($data->toArray())
            ->andReturn(new Question($data->toArray())); // Return a new Question instance

        $action = new CreateQuestionAction;

        // Act
        $question = $action->execute($data);

        // Assert
        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals($data->question, $question->question);
        $this->assertEquals($data->answer, $question->answer);
        $this->assertEquals($data->language, $question->language);
        $this->assertEquals($data->product_id, $question->product_id);
        $this->assertEquals($data->user_id, $question->user_id);
        $this->assertEquals($data->shop_id, $question->shop_id);
    }
}
