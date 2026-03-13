<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use Illuminate\Console\Command;

class AutoExpireWarranties extends Command
{
    protected $signature = 'warranties:expire';
    protected $description = 'Mark warranties as expired if past end_date';

    public function handle()
    {
        Warranty::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info('Expired warranties updated.');
    }
}