<?php

namespace App\Console\Commands;

use App\Jobs\SyncNocErrors;
use App\Jobs\SyncNocPlayback;
use App\Jobs\SyncNocSchedules;
use App\Models\NocInstance;
use Illuminate\Console\Command;

class HubSyncNow extends Command
{
    protected $signature = 'hub:sync-now
                            {--noc= : NOC instance ID (default: all active)}
                            {--type= : playback|schedules|errors (default: all)}';

    protected $description = 'Immediately sync NOC data to the hub (synchronous, no queue needed)';

    public function handle(): int
    {
        $nocId = $this->option('noc');
        $type  = $this->option('type');

        $nocs = $nocId
            ? NocInstance::where('id', $nocId)->where('is_active', true)->get()
            : NocInstance::where('is_active', true)->get();

        if ($nocs->isEmpty()) {
            $this->error('No active NOC instance found. Add one in Admin → NOC Instances.');
            return self::FAILURE;
        }

        foreach ($nocs as $noc) {
            $this->line('');
            $this->line("── <fg=cyan>{$noc->name}</> ({$noc->url})");

            if (!$type || $type === 'playback') {
                $this->line('   → Playback...');
                try {
                    (new SyncNocPlayback($noc))->handle();
                    $this->info('   ✓ Playback synced.');
                } catch (\Throwable $e) {
                    $this->error('   ✗ ' . $e->getMessage());
                }
            }

            if (!$type || $type === 'errors') {
                $this->line('   → Errors...');
                try {
                    (new SyncNocErrors($noc))->handle();
                    $this->info('   ✓ Errors synced.');
                } catch (\Throwable $e) {
                    $this->error('   ✗ ' . $e->getMessage());
                }
            }

            if (!$type || $type === 'schedules') {
                $this->line('   → Schedules...');
                try {
                    (new SyncNocSchedules($noc))->handle();
                    $this->info('   ✓ Schedules synced.');
                } catch (\Throwable $e) {
                    $this->error('   ✗ ' . $e->getMessage());
                }
            }
        }

        $this->line('');
        $this->info('Done. Refresh the hub to see updated data.');

        return self::SUCCESS;
    }
}
