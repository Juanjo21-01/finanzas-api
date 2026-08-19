<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatosPruebaSeeder extends Seeder
{
    private const PASSWORD = 'Finanzas2026!';

    /**
     * Seed deterministic dashboard data for two test users.
     */
    public function run(): void
    {
        // The data seeder can also be executed directly and still has its catalog.
        $this->call(CategoriaSeeder::class);

        $usuarios = [
            [
                'nombre' => 'Ana López',
                'email' => 'ana.pruebas@finanzas.test',
                'ingresos' => [
                    '2800.00',
                    '2950.00',
                    '2800.00',
                    '3100.00',
                    '3000.00',
                    '3250.00',
                ],
            ],
            [
                'nombre' => 'Carlos Méndez',
                'email' => 'carlos.pruebas@finanzas.test',
                'ingresos' => [
                    '2400.00',
                    '2600.00',
                    '2500.00',
                    '2750.00',
                    '2650.00',
                    '2900.00',
                ],
            ],
        ];

        $mesesConDatos = [1, 2, 3, 4, 5, 7];

        foreach ($usuarios as $definicion) {
            $usuario = User::updateOrCreate(
                ['email' => $definicion['email']],
                [
                    'name' => $definicion['nombre'],
                    'password' => self::PASSWORD,
                ]
            );

            foreach ($mesesConDatos as $indice => $mes) {
                $this->crearIngreso(
                    $usuario,
                    $mes,
                    $definicion['ingresos'][$indice]
                );

                $this->crearEgresos($usuario, $mes);
            }
        }
    }

    private function crearIngreso(User $usuario, int $mes, string $monto): void
    {
        $categorias = [
            'Empleo',
            'Freelance / Proyecto',
            'Empleo',
            'Bono / Extra',
            'Freelance / Proyecto',
            'Negocio Propio',
        ];

        $fuentes = [
            'Trabajo de medio tiempo',
            'Proyecto freelance',
            'Trabajo de medio tiempo',
            'Bono universitario',
            'Proyecto freelance',
            'Venta de proyecto propio',
        ];

        $fecha = sprintf('2026-%02d-05', $mes);
        $categoria = Categoria::query()
            ->whereNull('user_id')
            ->where('tipo', 'ingreso')
            ->where('nombre', $categorias[$mes === 7 ? 5 : $mes - 1])
            ->firstOrFail();

        $datos = Ingreso::factory()->raw([
            'user_id' => $usuario->id,
            'categoria_id' => $categoria->id,
            'fecha' => $fecha,
            'fuente' => $fuentes[$mes === 7 ? 5 : $mes - 1],
            'monto' => $monto,
            'notas' => 'Dato de prueba para el dashboard',
        ]);

        Ingreso::updateOrCreate(
            [
                'user_id' => $usuario->id,
                'fecha' => $fecha,
            ],
            $datos
        );
    }

    private function crearEgresos(User $usuario, int $mes): void
    {
        $plantillas = [
            ['Vivienda', 'Internet', 'Pago de internet', '350.00'],
            ['Educación', 'Cursos', 'Material y curso universitario', '180.00'],
            ['Alimentación', 'Supermercado', 'Compra de supermercado', '300.00'],
            ['Transporte', 'Bus', 'Transporte semanal', '120.00'],
            ['Salud', 'Medicamentos', 'Medicamentos', '85.00'],
            ['Ocio / Entretenimiento', 'Suscripciones', 'Suscripciones digitales', '60.00'],
            ['Deporte', 'Gimnasio', 'Membresía de gimnasio', '150.00'],
            ['Imprevistos', 'Reparaciones', 'Reparación menor', '125.00'],
            ['Otro Egreso', null, 'Gasto diverso', '75.00'],
        ];

        $offset = ($mes - 1) % count($plantillas);
        $dias = [3, 6, 9, 12, 15, 18, 21, 24];

        // Eight records per month: within the requested range of 6 to 12.
        for ($indice = 0; $indice < 8; $indice++) {
            $plantilla = $plantillas[($offset + $indice) % count($plantillas)];
            [$nombreCategoria, $nombreSubcategoria, $descripcion, $monto] = $plantilla;

            $categoria = Categoria::query()
                ->whereNull('user_id')
                ->where('tipo', 'egreso')
                ->where('nombre', $nombreCategoria)
                ->firstOrFail();

            $subcategoria = $nombreSubcategoria === null
                ? null
                : $categoria->subcategorias()
                    ->where('nombre', $nombreSubcategoria)
                    ->firstOrFail();

            $fecha = sprintf('2026-%02d-%02d', $mes, $dias[$indice]);
            $datos = Egreso::factory()->raw([
                'user_id' => $usuario->id,
                'categoria_id' => $categoria->id,
                'subcategoria_id' => $subcategoria?->id,
                'fecha' => $fecha,
                'descripcion' => $descripcion,
                'monto' => $monto,
                'notas' => 'Dato de prueba para el dashboard',
            ]);

            Egreso::updateOrCreate(
                [
                    'user_id' => $usuario->id,
                    'fecha' => $fecha,
                    'descripcion' => $descripcion,
                ],
                $datos
            );
        }
    }
}
