<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\IngresoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'categoria_id',
    'fecha',
    'fuente',
    'monto',
    'notas',
])]
class Ingreso extends Model
{
    /** @use HasFactory<IngresoFactory> */
    use HasFactory;

    protected $table = 'ingresos';

    /**
     * Get the user who owns the income.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the income category.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Scope incomes that occurred during a given calendar month.
     *
     * The upper bound is exclusive so the date column can use its index.
     */
    public function scopeDelMes(Builder $query, int $anio, int $mes): Builder
    {
        $inicio = CarbonImmutable::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->addMonth();

        return $query
            ->where('fecha', '>=', $inicio->toDateString())
            ->where('fecha', '<', $fin->toDateString());
    }

    /**
     * Get the model's attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }
}
