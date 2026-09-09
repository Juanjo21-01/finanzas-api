<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardEgresosPorCategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_the_authenticated_users_expenses_by_category_in_descending_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $alimentacion = $user->categorias()->create([
            'nombre' => 'Alimentación',
            'tipo' => 'egreso',
        ]);
        $vivienda = $user->categorias()->create([
            'nombre' => 'Vivienda',
            'tipo' => 'egreso',
        ]);
        $transporte = $user->categorias()->create([
            'nombre' => 'Transporte',
            'tipo' => 'egreso',
        ]);
        $otherCategory = $otherUser->categorias()->create([
            'nombre' => 'Otra categoría',
            'tipo' => 'egreso',
        ]);

        $user->egresos()->createMany([
            ['categoria_id' => $alimentacion->id, 'fecha' => '2026-03-01', 'descripcion' => 'Supermercado', 'monto' => '75.50'],
            ['categoria_id' => $alimentacion->id, 'fecha' => '2026-03-20', 'descripcion' => 'Restaurante', 'monto' => '24.50'],
            ['categoria_id' => $vivienda->id, 'fecha' => '2026-03-05', 'descripcion' => 'Renta', 'monto' => '500.00'],
            ['categoria_id' => $transporte->id, 'fecha' => '2026-02-28', 'descripcion' => 'Gasolina anterior', 'monto' => '999.00'],
            ['categoria_id' => $vivienda->id, 'fecha' => '2026-04-01', 'descripcion' => 'Renta futura', 'monto' => '9000.00'],
        ]);
        $otherUser->egresos()->create([
            'categoria_id' => $otherCategory->id,
            'fecha' => '2026-03-10',
            'descripcion' => 'Egreso ajeno',
            'monto' => '7000.00',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/egresos-por-categoria?anio=2026&mes=3')
            ->assertOk()
            ->assertExactJson([
                [
                    'categoria_id' => $vivienda->id,
                    'nombre' => 'Vivienda',
                    'total' => '500.00',
                ],
                [
                    'categoria_id' => $alimentacion->id,
                    'nombre' => 'Alimentación',
                    'total' => '100.00',
                ],
            ]);
    }

    public function test_returns_an_empty_array_when_the_month_has_no_expenses(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard/egresos-por-categoria?anio=2026&mes=3')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_validates_the_requested_period(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard/egresos-por-categoria?anio=2026&mes=13')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mes']);

        $this->getJson('/api/dashboard/egresos-por-categoria')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio', 'mes']);
    }

    public function test_uses_one_query_for_the_grouped_result(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->getJson('/api/dashboard/egresos-por-categoria?anio=2026&mes=3')
            ->assertOk();

        $this->assertCount(1, $queries);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/dashboard/egresos-por-categoria?anio=2026&mes=3')
            ->assertUnauthorized();
    }
}
