<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Seed the system categories and their subcategories.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Empleo',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Freelance / Proyecto',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Negocio Propio',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Inversión / Dividendos',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Bono / Extra',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Otro Ingreso',
                'tipo' => 'ingreso',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Vivienda',
                'tipo' => 'egreso',
                'subcategorias' => ['Alquiler', 'Agua', 'Luz', 'Internet'],
            ],
            [
                'nombre' => 'Educación',
                'tipo' => 'egreso',
                'subcategorias' => ['Universidad', 'Cursos', 'Libros'],
            ],
            [
                'nombre' => 'Alimentación',
                'tipo' => 'egreso',
                'subcategorias' => ['Supermercado', 'Restaurante', 'Almuerzo'],
            ],
            [
                'nombre' => 'Transporte',
                'tipo' => 'egreso',
                'subcategorias' => ['Gasolina', 'Bus', 'Taxi / Uber', 'Parqueo'],
            ],
            [
                'nombre' => 'Salud',
                'tipo' => 'egreso',
                'subcategorias' => ['Consulta', 'Medicamentos', 'Laboratorio'],
            ],
            [
                'nombre' => 'Ocio / Entretenimiento',
                'tipo' => 'egreso',
                'subcategorias' => ['Suscripciones', 'Cine', 'Salidas'],
            ],
            [
                'nombre' => 'Deporte',
                'tipo' => 'egreso',
                'subcategorias' => ['Gimnasio', 'Equipo deportivo'],
            ],
            [
                'nombre' => 'Imprevistos',
                'tipo' => 'egreso',
                'subcategorias' => ['Emergencias', 'Reparaciones'],
            ],
            [
                'nombre' => 'Otro Egreso',
                'tipo' => 'egreso',
                'subcategorias' => [],
            ],
        ];

        DB::transaction(function () use ($categorias): void {
            foreach ($categorias as $definicion) {
                $categoria = Categoria::firstOrCreate([
                    'user_id' => null,
                    'nombre' => $definicion['nombre'],
                    'tipo' => $definicion['tipo'],
                ]);

                foreach ($definicion['subcategorias'] as $nombre) {
                    $categoria->subcategorias()->firstOrCreate([
                        'nombre' => $nombre,
                    ]);
                }
            }
        });
    }
}
