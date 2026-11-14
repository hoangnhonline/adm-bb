<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** php artisan logs:cleanup — xóa request_log cũ hơn 12 tháng (activity_log giữ vĩnh viễn). */
class CleanupRequestLog extends Command
{
    protected $signature = 'logs:cleanup {--months=12}';
    protected $description = 'Xóa nhật ký thao tác (request_log) cũ hơn N tháng (mặc định 12)';

    public function handle()
    {
        $months = max(1, (int) $this->option('months'));
        $n = DB::table('request_log')->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-$months months")))->delete();
        $this->info("Đã xóa $n dòng request_log cũ hơn $months tháng");
    }
}
