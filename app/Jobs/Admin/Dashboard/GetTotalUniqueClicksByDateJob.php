<?php

namespace App\Jobs\Admin\Dashboard;

use App\Http\Classes\AdminStatistics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GetTotalUniqueClicksByDateJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $startDate;
    protected $endDate;
    protected $statService;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, AdminStatistics $statService)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->statService = $statService;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->statService->getTotalUniqueClicks($this->startDate, $this->endDate);
        Cache::put('total_unique_clicks_by_date', $data, now()->addMinutes(10));
    }
}
