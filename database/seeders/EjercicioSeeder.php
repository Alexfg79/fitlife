<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use Illuminate\Database\Seeder;

class EjercicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ejercicios = [
            // Pecho (5)
            [
                'nombre' => 'Press de Banca Plano con Barra',
                'grupo_muscular' => 'pecho',
                'descripcion' => 'Ejercicio básico multiarticular enfocado en el desarrollo del pectoral mayor.',
                'equipo_necesario' => 'Banco plano, barra olímpica y discos',
            ],
            [
                'nombre' => 'Press Inclinado con Mancuernas',
                'grupo_muscular' => 'pecho',
                'descripcion' => 'Desarrollo del haz clavicular superior del pectoral.',
                'equipo_necesario' => 'Banco inclinado 30° y mancuernas',
            ],
            [
                'nombre' => 'Cruces en Polea Alta',
                'grupo_muscular' => 'pecho',
                'descripcion' => 'Aislamiento continuo con tensión constante en la porción inferior del pecho.',
                'equipo_necesario' => 'Torre de poleas dobles',
            ],
            [
                'nombre' => 'Fondos en Paralelas (Dips)',
                'grupo_muscular' => 'pecho',
                'descripcion' => 'Autocarga corporal enfocada en la porción esternal inferior y tríceps.',
                'equipo_necesario' => 'Barras paralelas',
            ],
            [
                'nombre' => 'Aperturas Planas con Mancuernas',
                'grupo_muscular' => 'pecho',
                'descripcion' => 'Estiramiento profundo y aislamiento del pectoral en banco plano.',
                'equipo_necesario' => 'Mancuernas y banco plano',
            ],

            // Espalda (5)
            [
                'nombre' => 'Dominadas en Barra Fija',
                'grupo_muscular' => 'espalda',
                'descripcion' => 'Tracción vertical con peso corporal para dorsal ancho y redondo mayor.',
                'equipo_necesario' => 'Barra de dominadas',
            ],
            [
                'nombre' => 'Remo con Barra Pendlay',
                'grupo_muscular' => 'espalda',
                'descripcion' => 'Tracción horizontal desde el suelo para densidad de espalda media y alta.',
                'equipo_necesario' => 'Barra olímpica y discos',
            ],
            [
                'nombre' => 'Jalón al Pecho en Polea Alta',
                'grupo_muscular' => 'espalda',
                'descripcion' => 'Amplitud de dorsales con agarre prono abierto.',
                'equipo_necesario' => 'Máquina de jalón dorsal',
            ],
            [
                'nombre' => 'Remo Gironda en Polea Baja',
                'grupo_muscular' => 'espalda',
                'descripcion' => 'Grosor muscular en romboides, trapecio medio y zona lumbar.',
                'equipo_necesario' => 'Polea baja y agarre en V',
            ],
            [
                'nombre' => 'Remo Unilateral con Mancuerna (Serrucho)',
                'grupo_muscular' => 'espalda',
                'descripcion' => 'Trabajo unilateral para corregir asimetrías en el dorsal.',
                'equipo_necesario' => 'Mancuerna pesada y banco',
            ],

            // Piernas (6)
            [
                'nombre' => 'Sentadilla Trasera con Barra (Squat)',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'El rey de los ejercicios del tren inferior: cuádriceps, glúteos e isquiotibiales.',
                'equipo_necesario' => 'Rack de sentadillas, barra y discos',
            ],
            [
                'nombre' => 'Prensa de Piernas 45 Grados',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'Sobrecarga controlada de cuádriceps y glúteos con respaldo lumbar.',
                'equipo_necesario' => 'Máquina prensa 45° y discos',
            ],
            [
                'nombre' => 'Peso Muerto Rumano con Mancuernas',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'Bisagra de cadera con énfasis en glúteos e isquiosurales.',
                'equipo_necesario' => 'Mancuernas',
            ],
            [
                'nombre' => 'Extensiones de Cuádriceps en Máquina',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'Aislamiento concéntrico de cuádriceps y recto femoral.',
                'equipo_necesario' => 'Máquina de extensiones',
            ],
            [
                'nombre' => 'Curl Femoral Tumbado',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'Flexión de rodilla aislada para bíceps femoral y semitendinoso.',
                'equipo_necesario' => 'Máquina de femoral tumbado',
            ],
            [
                'nombre' => 'Elevación de Talones en Máquina de Gemelos',
                'grupo_muscular' => 'piernas',
                'descripcion' => 'Hipertrofia de gastrocnemios y sóleo con extensión plantar.',
                'equipo_necesario' => 'Máquina de gemelos de pie',
            ],

            // Hombros (5)
            [
                'nombre' => 'Press Militar con Barra de Pie (OHP)',
                'grupo_muscular' => 'hombros',
                'descripcion' => 'Empuje vertical estricto para deltoides anterior, lateral y core.',
                'equipo_necesario' => 'Barra olímpica',
            ],
            [
                'nombre' => 'Elevaciones Laterales con Mancuernas',
                'grupo_muscular' => 'hombros',
                'descripcion' => 'Aislamiento del deltoides lateral para crear amplitud de hombro.',
                'equipo_necesario' => 'Mancuernas',
            ],
            [
                'nombre' => 'Pájaros con Mancuernas (Deltoides Posterior)',
                'grupo_muscular' => 'hombros',
                'descripcion' => 'Flexión de cadera y apertura horizontal para deltoides posterior.',
                'equipo_necesario' => 'Mancuernas ligeras',
            ],
            [
                'nombre' => 'Press Arnold con Mancuernas',
                'grupo_muscular' => 'hombros',
                'descripcion' => 'Rotación continua que estimula las tres cabezas del deltoides.',
                'equipo_necesario' => 'Mancuernas y banco 90°',
            ],
            [
                'nombre' => 'Face Pulls en Polea con Cuerda',
                'grupo_muscular' => 'hombros',
                'descripcion' => 'Salud articular de manguito rotador y deltoides posterior.',
                'equipo_necesario' => 'Polea alta y cuerda',
            ],

            // Brazos (6)
            [
                'nombre' => 'Curl de Bíceps con Barra Z',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Flexión de codo ergonómica para masa global del bíceps.',
                'equipo_necesario' => 'Barra Z y discos',
            ],
            [
                'nombre' => 'Press Francés con Barra Z en Banco',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Aislamiento de la cabeza larga y media del tríceps braquial.',
                'equipo_necesario' => 'Banco plano y barra Z',
            ],
            [
                'nombre' => 'Curl Martillo con Mancuernas',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Enfoque en braquial anterior y braquiorradial del antebrazo.',
                'equipo_necesario' => 'Mancuernas',
            ],
            [
                'nombre' => 'Extensiones de Tríceps en Polea con Cuerda',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Contracción pico con apertura de cuerda al final del rango.',
                'equipo_necesario' => 'Polea alta y cuerda',
            ],
            [
                'nombre' => 'Curl Concentrado en Banco Scott',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Aislamiento estricto sin balanceo del bíceps braquial.',
                'equipo_necesario' => 'Banco Scott y barra o mancuerna',
            ],
            [
                'nombre' => 'Fondos entre Bancos para Tríceps',
                'grupo_muscular' => 'brazos',
                'descripcion' => 'Empuje de tríceps con peso corporal o disco adicional.',
                'equipo_necesario' => 'Dos bancos planos',
            ],

            // Abdomen / Core (4)
            [
                'nombre' => 'Plancha Abdominal Isométrica (Plank)',
                'grupo_muscular' => 'core',
                'descripcion' => 'Activación isométrica del transverso del abdomen y estabilizadores espinales.',
                'equipo_necesario' => 'Esterilla / Mat',
            ],
            [
                'nombre' => 'Elevaciones de Piernas Colgado en Barra',
                'grupo_muscular' => 'core',
                'descripcion' => 'Flexión de cadera y pelvis para el recto abdominal inferior.',
                'equipo_necesario' => 'Barra de dominadas',
            ],
            [
                'nombre' => 'Rueda Abdominal (Ab Wheel Rollout)',
                'grupo_muscular' => 'core',
                'descripcion' => 'Antiextensión abdominal de alta intensidad biomecánica.',
                'equipo_necesario' => 'Rueda abdominal',
            ],
            [
                'nombre' => 'Giros Rusos con Balón Medicinal',
                'grupo_muscular' => 'core',
                'descripcion' => 'Rotación del torso con carga para oblicuos internos y externos.',
                'equipo_necesario' => 'Balón medicinal o disco',
            ],

            // Cardio (4)
            [
                'nombre' => 'Sprints en Cinta de Correr (HIIT)',
                'grupo_muscular' => 'cardio',
                'descripcion' => 'Intervalos de alta intensidad para capacidad VO2 Max y gasto calórico.',
                'equipo_necesario' => 'Cinta de correr motorizada',
            ],
            [
                'nombre' => 'Remoergómetro (Rowing Machine Concept2)',
                'grupo_muscular' => 'cardio',
                'descripcion' => 'Cardio de cuerpo completo sin impacto articular.',
                'equipo_necesario' => 'Máquina de remo',
            ],
            [
                'nombre' => 'Bicicleta de Aire (Air Bike Assault)',
                'grupo_muscular' => 'cardio',
                'descripcion' => 'Resistencia por aire que escala exponencialmente con el esfuerzo.',
                'equipo_necesario' => 'Air Bike',
            ],
            [
                'nombre' => 'Saltos con Cuerda de Velocidad (Speed Rope)',
                'grupo_muscular' => 'cardio',
                'descripcion' => 'Coordinación motora, agilidad y quema calórica continua.',
                'equipo_necesario' => 'Cuerda de saltar',
            ],
        ];

        foreach ($ejercicios as $ejercicio) {
            Ejercicio::updateOrCreate(
                ['nombre' => $ejercicio['nombre']],
                $ejercicio
            );
        }
    }
}
