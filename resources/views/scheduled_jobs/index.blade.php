<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Jobs de préstamos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .ok { color: #2e7d32; font-weight: bold; }
        .pending { color: #c62828; font-weight: bold; }
        .results { background: #f5f5f5; padding: 12px; border-radius: 4px; }
        button { padding: 10px 18px; background: #1565c0; color: #fff; border: 0; border-radius: 4px; cursor: pointer; }
        button:disabled { background: #999; }
    </style>
</head>
<body>
    <h2>Jobs diarios de préstamos ({{ now()->format('d/m/Y H:i') }})</h2>

    @if(!empty($results))
        <div class="results">
            @foreach($results as $result)
                <div>{{ $result }}</div>
            @endforeach
        </div>
    @endif

    <table>
        <tr><th>Comando</th><th>Estado hoy</th></tr>
        @foreach($status as $command => $ran)
            <tr>
                <td>{{ $command }}</td>
                <td class="{{ $ran ? 'ok' : 'pending' }}">{{ $ran ? 'Ejecutado' : 'Pendiente' }}</td>
            </tr>
        @endforeach
    </table>

    @if(in_array(false, $status, true))
        <form method="POST" action="{{ route('scheduled-jobs.run') }}"
              onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerText = 'Ejecutando...';">
            @csrf
            <p>Se ejecutarán solo los pendientes, en orden: primero la mora y luego el SMS.</p>
            <button type="submit">Ejecutar pendientes</button>
        </form>
    @else
        <p class="ok">Todos los jobs de hoy ya se ejecutaron.</p>
    @endif
</body>
</html>
