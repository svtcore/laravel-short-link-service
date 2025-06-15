<?php

namespace App\Jobs\Admin\Dashboard;

use App\Http\Services\AdminStatisticsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GetTotalClicksByDateJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $startDate;

    protected $endDate;

    protected $statService;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, AdminStatisticsService $statService)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->statService = $statService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $key = 'total_clicks_by_date_'.$this->startDate->toDateString().'_'.$this->endDate->toDateString();
        $data = $this->statService->getTotalClicksByDate($this->startDate, $this->endDate);

        if ($this->endDate->isToday()) {
            Cache::put($key, $data, now()->addDay());
        } else {
            Cache::forever($key, $data);
        }
    }
}
