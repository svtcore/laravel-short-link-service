<?php

namespace Tests\Unit\Http\Requests\Admin\Links;

use App\Http\Requests\Admin\Links\ShowRequest;
use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Routing\Route;
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
            'id' => Link::factory()->create()->id,
            'startDate' => Carbon::yesterday()->format('Y-m-d'),
            'endDate' => Carbon::today()->format('Y-m-d'),
        ];
    }

    #[Test]
    public function it_prepares_data_correctly()
    {
        $request = new ShowRequest;
        $route = new Route('GET', '/links/{id}', []);
        $route->bind($request);
        $route->setParameter('id', 123);
        $request->setRouteResolver(fn () => $route);

        $request->prepareForValidation();

        $this->assertEquals(['id' => 123], $request->all());
    }

    #[Test]
    public function it_validates_correct_data()
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_fails_on_missing_link_id()
    {
        $this->assertValidationFails(
            [],
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
    public function it_fails_on_invalid_date_formats()
    {
        $this->assertValidationFails(
            ['id' => 1, 'startDate' => 'invalid-date', 'endDate' => '2025-01-01'],
            ['startDate' => 'The start date field must be a valid date.']
        );

        $this->assertValidationFails(
            ['id' => 1, 'startDate' => '2025-01-01', 'endDate' => 'invalid-date'],
            ['endDate' => 'The end date field must be a valid date.']
        );
    }

    #[Test]
    public function it_fails_on_start_date_after_end_date()
    {
        $this->assertValidationFails(
            ['id' => 1, 'startDate' => '2025-01-02', 'endDate' => '2025-01-01'],
            ['startDate' => 'The start date field must be a date before or equal to end date.']
        );
    }

    #[Test]
    public function it_fails_on_future_dates()
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');
        $this->assertValidationFails(
            ['id' => 1, 'startDate' => $futureDate, 'endDate' => $futureDate],
            ['startDate' => 'The start date field must be a date before or equal to today.']
        );
    }

    #[Test]
    public function it_accepts_null_dates()
    {
        $data = $this->getValidData();
        unset($data['startDate'], $data['endDate']);

        $this->assertValidationPasses($data);
    }
}
