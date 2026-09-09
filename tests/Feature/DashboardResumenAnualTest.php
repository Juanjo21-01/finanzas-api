<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardResumenAnualTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_twelve_months_including_empty_months_and_only_the_authenticated_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $categoriaIngreso = $user->categorias()->create([
            'nombre' => 'Salario',
            'tipo' => 'ingreso',
        ]);
        $categoriaEgreso = $user->categorias()->create([
            'nombre' => 'Vivienda',
            'tipo' => 'egreso',
        ]);
        $otherCategoriaIngreso = $otherUser->categorias()->create([
            'nombre' => 'Ingreso ajeno',
            'tipo' => 'ingreso',
        ]);

        $user->ingresos()->create([
            'categoria_id' => $categoriaIngreso->id,
            'fecha' => '2026-01-10',
            'fuente' => 'Salario',
            'monto' => '1000.00',
        ]);
        $user->ingresos()->create([
            'categoria_id' => $categoriaIngreso->id,
            'fecha' => '2026-12-10',
            'fuente' => 'Bono',
            'monto' => '250.00',
        ]);
        $user->egresos()->create([
            'categoria_id' => $categoriaEgreso->id,
            'fecha' => '2026-01-15',
            'descripcion' => 'Renta',
            'monto' => '400.00',
        ]);
        $user->egresos()->create([
            'categoria_id' => $categoriaEgreso->id,
            'fecha' => '2026-06-15',
            'descripcion' => 'Renta',
            'monto' => '100.00',
        ]);
        $otherUser->ingresos()->create([
            'categoria_id' => $otherCategoriaIngreso->id,
            'fecha' => '2026-06-15',
            'fuente' => 'Ingreso ajeno',
            'monto' => '9000.00',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/resumen-anual?anio=2026');

        $response->assertOk()->assertJsonCount(12);
        $response->assertJsonPath('0', [
            'mes' => 1,
            'ingresos' => '1000.00',
            'egresos' => '400.00',
            'balance' => '600.00',
        ]);
        $response->assertJsonPath('1', [
            'mes' => 2,
            'ingresos' => '0.00',
            'egresos' => '0.00',
            'balance' => '0.00',
        ]);
        $response->assertJsonPath('5', [
            'mes' => 6,
            'ingresos' => '0.00',
            'egresos' => '100.00',
            'balance' => '-100.00',
        ]);
        $response->assertJsonPath('11', [
            'mes' => 12,
            'ingresos' => '250.00',
            'egresos' => '0.00',
            'balance' => '250.00',
        ]);
    }

    public function test_returns_twelve_zeroed_months_when_there_are_no_records(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/dashboard/resumen-anual?anio=2026');

        $response->assertOk()->assertJsonCount(12);
        foreach (range(0, 11) as $indice) {
            $response->assertJsonPath("$indice.ingresos", '0.00');
            $response->assertJsonPath("$indice.egresos", '0.00');
            $response->assertJsonPath("$indice.balance", '0.00');
        }
    }

    public function test_validates_the_year(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard/resumen-anual?anio=21')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio']);

        $this->getJson('/api/dashboard/resumen-anual')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio']);
    }

    public function test_uses_one_query_for_the_annual_aggregation(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->getJson('/api/dashboard/resumen-anual?anio=2026')
            ->assertOk();

        $this->assertCount(1, $queries);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/dashboard/resumen-anual?anio=2026')
            ->assertUnauthorized();
    }
}
