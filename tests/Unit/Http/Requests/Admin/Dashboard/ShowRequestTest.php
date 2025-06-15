<?php

namespace Tests\Unit\Http\Requests\Admin\Dashboard;

use App\Http\Requests\Admin\Dashboard\ShowRequest;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Http\Requests\RequestTestCase;

class ShowRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return ShowRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'startDate' => Carbon::yesterday()->format('Y-m-d'),
            'endDate' => Carbon::today()->format('Y-m-d'),
        ];
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_accepts_null_dates()
    {
        $this->assertValidationPasses([]);
    }

    #[Test]
    public function it_fails_on_invalid_start_date_format()
    {
        $this->assertValidationFails(
            ['startDate' => 'invalid-date', 'endDate' => '2025-01-01'],
            ['startDate' => 'The start date field must be a valid date.']
        );
    }

    #[Test]
    public function it_fails_on_start_date_after_end_date()
    {
        $this->assertValidationFails(
            [
                'startDate' => '2025-01-02',
                'endDate' => '2025-01-01',
            ],
            ['startDate' => 'The start date field must be a date before or equal to end date.']
        );
    }

    #[Test]
    public function it_fails_on_start_date_in_future()
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');
        $this->assertValidationFails(
            ['startDate' => $futureDate, 'endDate' => $futureDate],
            ['startDate' => 'The start date field must be a date before or equal to today.']
        );
    }

    #[Test]
    public function it_fails_on_end_date_in_future()
    {
        $this->assertValidationFails(
            [
                'startDate' => '2025-01-01',
                'endDate' => Carbon::tomorrow()->format('Y-m-d'),
            ],
            ['endDate' => 'The end date field must be a date before or equal to today.']
        );
    }
}
