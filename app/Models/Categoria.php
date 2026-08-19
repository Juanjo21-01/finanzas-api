<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre',
    'tipo',
])]
class Categoria extends Model
{
    protected $table = 'categorias';

    /**
     * Get the user who owns this category.
     *
     * A null user_id identifies a system category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subcategories of this category.
     */
    public function subcategorias(): HasMany
    {
        return $this->hasMany(Subcategoria::class);
    }

    /**
     * Get the incomes assigned to this category.
     */
    public function ingresos(): HasMany
    {
        return $this->hasMany(Ingreso::class);
    }

    /**
     * Get the expenses assigned to this category.
     */
    public function egresos(): HasMany
    {
        return $this->hasMany(Egreso::class);
    }

    /**
     * Scope categories visible to a user: system categories and own ones.
     */
    public function scopeVisiblesPara(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId): void {
            $query->whereNull('user_id')
                ->orWhere('user_id', $userId);
        });
    }
}
