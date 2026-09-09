<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest\DashboardEgresosPorCategoriaRequest;
use App\Http\Requests\DashboardRequest\DashboardResumenRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return the authenticated user's monthly and year-to-date summary.
     */
    public function resumen(DashboardResumenRequest $request): JsonResponse
    {
        $filtros = $request->validated();
        $anio = (int) $filtros['anio'];
        $mes = (int) $filtros['mes'];
        $userId = (int) $request->user()->getAuthIdentifier();

        $inicioAnio = CarbonImmutable::create($anio, 1, 1)->toDateString();
        $inicioMes = CarbonImmutable::create($anio, $mes, 1)->toDateString();
        $finMes = CarbonImmutable::create($anio, $mes, 1)->addMonth()->toDateString();

        $ingresos = DB::table('ingresos')
            ->select(['fecha', 'monto'])
            ->selectRaw('? AS tipo', ['ingreso'])
            ->where('user_id', $userId)
            ->where('fecha', '>=', $inicioAnio)
            ->where('fecha', '<', $finMes);

        $egresos = DB::table('egresos')
            ->select(['fecha', 'monto'])
            ->selectRaw('? AS tipo', ['egreso'])
            ->where('user_id', $userId)
            ->where('fecha', '>=', $inicioAnio)
            ->where('fecha', '<', $finMes);

        $movimientos = $ingresos->unionAll($egresos);

        $sumas = DB::query()
            ->fromSub($movimientos, 'movimientos')
            ->selectRaw(
                <<<'SQL'
                    COALESCE(SUM(CASE WHEN tipo = 'ingreso' AND fecha >= ? THEN monto ELSE 0 END), 0) AS ingresos_mes,
                    COALESCE(SUM(CASE WHEN tipo = 'egreso' AND fecha >= ? THEN monto ELSE 0 END), 0) AS egresos_mes,
                    COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) AS ingresos_acumulados,
                    COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END), 0) AS egresos_acumulados
                    SQL,
                [$inicioMes, $inicioMes]
            );

        $resumen = DB::query()
            ->fromSub($sumas, 'sumas')
            ->selectRaw(
                <<<'SQL'
                    ingresos_mes,
                    egresos_mes,
                    ingresos_mes - egresos_mes AS balance_mes,
                    ingresos_acumulados,
                    egresos_acumulados,
                    ingresos_acumulados - egresos_acumulados AS balance_acumulado,
                    CASE
                        WHEN ingresos_mes = 0 THEN 0
                        ELSE ROUND((egresos_mes * 100.00) / ingresos_mes, 2)
                    END AS porcentaje_gastado
                    SQL
            )
            ->first();

        return response()->json([
            'ingresos_mes' => $this->decimal($resumen->ingresos_mes),
            'egresos_mes' => $this->decimal($resumen->egresos_mes),
            'balance_mes' => $this->decimal($resumen->balance_mes),
            'ingresos_acumulados' => $this->decimal($resumen->ingresos_acumulados),
            'egresos_acumulados' => $this->decimal($resumen->egresos_acumulados),
            'balance_acumulado' => $this->decimal($resumen->balance_acumulado),
            'porcentaje_gastado' => $this->decimal($resumen->porcentaje_gastado),
        ]);
    }

    /**
     * Return the authenticated user's expenses grouped by category for a month.
     */
    public function egresosPorCategoria(DashboardEgresosPorCategoriaRequest $request): JsonResponse
    {
        $filtros = $request->validated();
        $anio = (int) $filtros['anio'];
        $mes = (int) $filtros['mes'];
        $userId = (int) $request->user()->getAuthIdentifier();

        $egresos = $request->user()
            ->egresos()
            ->join('categorias', 'categorias.id', '=', 'egresos.categoria_id')
            ->where(function ($query) use ($userId): void {
                $query->whereNull('categorias.user_id')
                    ->orWhere('categorias.user_id', $userId);
            })
            ->delMes($anio, $mes)
            ->select([
                'egresos.categoria_id',
                'categorias.nombre',
            ])
            ->selectRaw('SUM(egresos.monto) AS total')
            ->groupBy('egresos.categoria_id', 'categorias.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($egreso): array => [
                'categoria_id' => (int) $egreso->categoria_id,
                'nombre' => $egreso->nombre,
                'total' => $this->decimal($egreso->total),
            ])
            ->values();

        return response()->json($egresos);
    }

    /**
     * Preserve decimal precision in JSON without converting values to float.
     */
    private function decimal(mixed $value): string
    {
        $value = (string) $value;
        [$entero, $decimales] = array_pad(explode('.', $value, 2), 2, '');

        return $entero.'.'.str_pad(substr($decimales, 0, 2), 2, '0');
    }
}
