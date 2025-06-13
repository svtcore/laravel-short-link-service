<?php

namespace Tests\Unit\Http\Requests\User\Links;

use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\User\Links\ShowRequest;
use App\Models\Link;
use Illuminate\Container\Container;

class ShowRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return ShowRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'id' => Link::factory()->create()->id,
            'startDate' => '2025-01-01',
            'endDate' => '2025-01-31'
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_id()
    {
        $this->assertValidationFails(
            ['startDate' => '2025-01-01'],
            ['id' => 'The id field is required.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_link_id()
    {
        $this->assertValidationFails(
            ['id' => 999999],
            ['id' => 'The selected id is invalid.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_start_date_format()
    {
        $this->assertValidationFails(
            ['startDate' => 'not-a-date'],
            ['startDate' => 'The start date field must be a valid date.']
        );
    }

    #[Test]
    public function it_fails_on_start_date_after_end_date()
    {
        $this->assertValidationFails(
            [
                'startDate' => '2025-02-01',
                'endDate' => '2025-01-01'
            ],
            ['startDate' => 'The start date field must be a date before or equal to end date.']
        );
    }

    #[Test]
    public function it_fails_on_start_date_in_future()
    {
        $this->assertValidationFails(
            ['startDate' => '2030-01-01'],
            ['startDate' => 'The start date field must be a date before or equal to today.']
        );
    }

    #[Test]
    public function it_fails_on_invalid_end_date_format()
    {
        $this->assertValidationFails(
            ['endDate' => 'not-a-date'],
            ['endDate' => 'The end date field must be a valid date.']
        );
    }

    #[Test]
    public function it_fails_on_end_date_before_start_date()
    {
        $this->assertValidationFails(
            [
                'startDate' => '2025-01-15',
                'endDate' => '2025-01-01'
            ],
            ['endDate' => 'The end date field must be a date after or equal to start date.']
        );
    }

    #[Test]
    public function it_fails_on_end_date_in_future()
    {
        $this->assertValidationFails(
            ['endDate' => '2030-01-01'],
            ['endDate' => 'The end date field must be a date before or equal to today.']
        );
    }

    #[Test]
    public function it_accepts_null_dates()
    {
        $data = $this->getValidData();
        $data['startDate'] = null;
        $data['endDate'] = null;
        
        $this->assertValidationPasses($data);
    }
}
