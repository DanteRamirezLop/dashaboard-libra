<?php

namespace App\Http\Controllers;

use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Permite ejecutar manualmente los jobs diarios de préstamos cuando el
 * scheduler no corrió. Solo se ejecuta un job si su mensaje de éxito
 * no aparece en el log de hoy, para no duplicar la mora.
 */
class ScheduledJobController extends Controller
{
    /**
     * Orden obligatorio: primero la mora, luego el SMS (que crea las moras nuevas).
     * Si se invierte, las moras creadas hoy se incrementarían a 2 días el mismo día.
     */
    protected $jobs = [
        'pos:calculateStatusLoan' => 'MiJob de MORA se ejecutó correctamente',
        'pos:smsPagoVencer' => 'JOB de SMS se ejecuto correctamente',
    ];

    protected $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $this->authorizeAdmin();

        return view('scheduled_jobs.index', [
            'status' => $this->jobStatus(),
            'results' => session('job_results'),
        ]);
    }

    public function run(Request $request)
    {
        $this->authorizeAdmin();

        // Evita dos ejecuciones simultáneas (doble clic, dos pestañas).
        $lock = Cache::lock('manual_loan_jobs', 600);
        if (! $lock->get()) {
            return redirect()->route('scheduled-jobs.index')
                ->with('job_results', ['Ya hay una ejecución en curso, espera a que termine.']);
        }

        set_time_limit(0);
        ignore_user_abort(true);

        $results = [];
        try {
            foreach ($this->jobs as $command => $successMessage) {
                if ($this->ranToday($successMessage)) {
                    $results[] = "{$command}: OMITIDO, ya se ejecutó hoy.";
                    continue;
                }

                Log::info("Ejecución manual de {$command} por el usuario #" . auth()->id());
                Artisan::call($command);

                $results[] = $this->ranToday($successMessage)
                    ? "{$command}: EJECUTADO correctamente."
                    : "{$command}: TERMINÓ SIN el mensaje de éxito, revisa el log de hoy.";
            }
        } finally {
            $lock->release();
        }

        return redirect()->route('scheduled-jobs.index')->with('job_results', $results);
    }

    protected function jobStatus()
    {
        $status = [];
        foreach ($this->jobs as $command => $successMessage) {
            $status[$command] = $this->ranToday($successMessage);
        }

        return $status;
    }

    /**
     * Revisa si el mensaje de éxito del job aparece en el log diario de hoy.
     */
    protected function ranToday($successMessage)
    {
        $file = storage_path('logs/laravel-' . now()->toDateString() . '.log');
        if (! is_file($file)) {
            return false;
        }

        $handle = fopen($file, 'r');
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, $successMessage) !== false) {
                fclose($handle);
                return true;
            }
        }
        fclose($handle);

        return false;
    }

    protected function authorizeAdmin()
    {
        if (! $this->util->is_admin(auth()->user())) {
            abort(403, 'Unauthorized action.');
        }
    }
}
