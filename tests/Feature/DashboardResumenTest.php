<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_only_includes_the_authenticated_user_up_to_the_requested_month(): void
    {
        $user = User::factory()->create();
        $otroUser = User::factory()->create();
        $categoriaIngreso = Categoria::query()->create([
            'nombre' => 'Salario',
            'tipo' => 'ingreso',
        ]);
        $categoriaEgreso = Categoria::query()->create([
            'nombre' => 'Vivienda',
            'tipo' => 'egreso',
        ]);

        $user->ingresos()->createMany([
            ['categoria_id' => $categoriaIngreso->id, 'fecha' => '2026-01-10', 'fuente' => 'Salario', 'monto' => '1000.00'],
            ['categoria_id' => $categoriaIngreso->id, 'fecha' => '2026-03-10', 'fuente' => 'Salario', 'monto' => '2000.00'],
            ['categoria_id' => $categoriaIngreso->id, 'fecha' => '2026-04-10', 'fuente' => 'Futuro', 'monto' => '9000.00'],
            ['categoria_id' => $categoriaIngreso->id, 'fecha' => '2025-03-10', 'fuente' => 'Otro anio', 'monto' => '8000.00'],
        ]);
        $user->egresos()->createMany([
            ['categoria_id' => $categoriaEgreso->id, 'fecha' => '2026-02-05', 'descripcion' => 'Renta', 'monto' => '250.00'],
            ['categoria_id' => $categoriaEgreso->id, 'fecha' => '2026-03-05', 'descripcion' => 'Renta', 'monto' => '500.00'],
            ['categoria_id' => $categoriaEgreso->id, 'fecha' => '2026-04-05', 'descripcion' => 'Futuro', 'monto' => '7000.00'],
        ]);
        $otroUser->ingresos()->create([
            'categoria_id' => $categoriaIngreso->id,
            'fecha' => '2026-03-15',
            'fuente' => 'Ingreso ajeno',
            'monto' => '6000.00',
        ]);
        $otroUser->egresos()->create([
            'categoria_id' => $categoriaEgreso->id,
            'fecha' => '2026-03-15',
            'descripcion' => 'Egreso ajeno',
            'monto' => '6000.00',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/resumen?anio=2026&mes=3');

        $response->assertOk()->assertExactJson([
            'ingresos_mes' => '2000.00',
            'egresos_mes' => '500.00',
            'balance_mes' => '1500.00',
            'ingresos_acumulados' => '3000.00',
            'egresos_acumulados' => '750.00',
            'balance_acumulado' => '2250.00',
            'porcentaje_gastado' => '25.00',
        ]);
    }

    public function test_resume_returns_zeroes_when_there_are_no_records_or_monthly_income(): void
    {
        $user = User::factory()->create();
        $categoriaEgreso = Categoria::query()->create([
            'nombre' => 'Servicios',
            'tipo' => 'egreso',
        ]);

        $user->egresos()->create([
            'categoria_id' => $categoriaEgreso->id,
            'fecha' => '2026-02-05',
            'descripcion' => 'Electricidad',
            'monto' => '100.00',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/resumen?anio=2026&mes=3')
            ->assertOk()
            ->assertExactJson([
                'ingresos_mes' => '0.00',
                'egresos_mes' => '0.00',
                'balance_mes' => '0.00',
                'ingresos_acumulados' => '0.00',
                'egresos_acumulados' => '100.00',
                'balance_acumulado' => '-100.00',
                'porcentaje_gastado' => '0.00',
            ]);

        $this->getJson('/api/dashboard/resumen?anio=2027&mes=3')
            ->assertOk()
            ->assertExactJson([
                'ingresos_mes' => '0.00',
                'egresos_mes' => '0.00',
                'balance_mes' => '0.00',
                'ingresos_acumulados' => '0.00',
                'egresos_acumulados' => '0.00',
                'balance_acumulado' => '0.00',
                'porcentaje_gastado' => '0.00',
            ]);
    }

    public function test_resume_validates_year_and_month_with_a_form_request(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard/resumen?anio=2026&mes=13')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mes']);

        $this->getJson('/api/dashboard/resumen')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['anio', 'mes']);
    }

    public function test_resume_uses_one_data_query(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->getJson('/api/dashboard/resumen?anio=2026&mes=3')->assertOk();

        $this->assertCount(1, $queries);
    }

    public function test_resume_requires_authentication(): void
    {
        $this->getJson('/api/dashboard/resumen?anio=2026&mes=3')
            ->assertUnauthorized();
    }
}
