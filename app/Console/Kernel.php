<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $env = config('app.env');
        $email = config('mail.username');

        if ($env === 'live') {
            //Loans - custom jobs
            $this->monitored($schedule->command('pos:calculateStatusLoan')->dailyAt('00:05'), 'pos:calculateStatusLoan');
            $this->monitored($schedule->command('pos:smsPagoVencer')->dailyAt('00:10'), 'pos:smsPagoVencer');
            $this->monitored($schedule->command('pos:exchangerate')->dailyAt('00:15'), 'pos:exchangerate');
        
            //Scheduling backup, specify the time when the backup will get cleaned & time when it will run.
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
            //Schedule to create recurring invoices
            $schedule->command('pos:generateSubscriptionInvoices')->dailyAt('23:30');
            $schedule->command('pos:updateRewardPoints')->dailyAt('23:45');
            $schedule->command('pos:autoSendPaymentReminder')->dailyAt('8:00');
        }

        if ($env === 'demo') {
            //IMPORTANT NOTE: This command will delete all business details and create dummy business, run only in demo server.
            $schedule->command('pos:dummyBusiness')
                    ->cron('0 */3 * * *')
                    //->everyThirtyMinutes()
                    ->emailOutputTo($email);
        }

    }

    /**
     * Guarda la salida del job en storage/logs/scheduler.log, registra en el log
     * cuando falla y, si está configurado, avisa a un monitor externo al terminar bien.
     */
    protected function monitored(Event $event, string $command): Event
    {
        $event->appendOutputTo(storage_path('logs/scheduler.log'))
            ->before(function () use ($command) {
                Log::info("SCHEDULER: inicia {$command}");
            })
            ->onFailure(function () use ($command) {
                Log::emergency("SCHEDULER: el job {$command} terminó con error, revisar storage/logs/scheduler.log");
            });

        $pingUrl = config("services.scheduler_ping.{$command}");
        if (! empty($pingUrl)) {
            $event->pingOnSuccess($pingUrl);
        }

        return $event;
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
