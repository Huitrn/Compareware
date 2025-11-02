<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periferico;
use App\Models\Categoria;
use App\Models\Comentario;

class ApiPerifericoController extends Controller
{
    // 1. Listado de periféricos con filtros opcionales
    public function index(Request $request)
    {
        // Validar y sanitizar parámetros
        $categoria = $request->has('categoria') ? $this->validateNumeric($request->categoria) : null;
        $marca = $request->has('marca') ? $this->sanitizeInput($request->marca) : null;

        $query = Periferico::query();

        if ($categoria && $categoria > 0) {
            $query->where('categoria_id', $categoria);
        }
        if ($marca) {
            $query->where('marca', $marca);
        }

        // Log de acceso
        \Log::info('PERIFERICOS_API_ACCESS', [
            'ip' => $request->ip(),
            'filters' => ['categoria' => $categoria, 'marca' => $marca]
        ]);

        return response()->json($query->get());
    }

    /**
     * Validar entrada numérica
     */
    private function validateNumeric($input): ?int
    {
        if (is_null($input) || $input === '') {
            return null;
        }

        if (!is_numeric($input) || $input < 0 || $input > 999999) {
            \Log::warning('INVALID_NUMERIC_INPUT_API', [
                'input' => $input,
                'ip' => request()->ip()
            ]);
            return null;
        }

        return (int) $input;
    }

    /**
     * Sanitizar entrada de texto
     */
    private function sanitizeInput($input): string
    {
        if (is_null($input)) {
            return '';
        }

        $input = (string) $input;

        // Detectar y remover patrones peligrosos
        $dangerous_patterns = [
            '/[\'";\\\\]/',
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b/i',
            '/(-{2,}|\/\*|\*\/|\#)/',
            '/<[^>]*>/',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];

        $originalInput = $input;
        foreach ($dangerous_patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        if ($input !== $originalInput) {
            \Log::warning('MALICIOUS_INPUT_DETECTED_API', [
                'original' => $originalInput,
                'sanitized' => $input,
                'ip' => request()->ip()
            ]);
        }

        return trim($input);
    }

    // 2. Detalle de periférico
    public function show($id)
    {
        $periferico = Periferico::find($id);
        if (!$periferico) {
            return response()->json(['error' => 'No encontrado'], 404);
        }
        return response()->json($periferico);
    }

    // 3. Comparación de periféricos - SEGURO
    public function comparar(Request $request)
    {
        // Validar IDs
        $id1 = $this->validateNumeric($request->get('periferico1'));
        $id2 = $this->validateNumeric($request->get('periferico2'));

        if (!$id1 || !$id2 || $id1 === $id2) {
            return response()->json([
                'error' => 'IDs de periféricos inválidos o iguales'
            ], 400);
        }

        $p1 = Periferico::find($id1);
        $p2 = Periferico::find($id2);

        if (!$p1 || !$p2) {
            return response()->json(['error' => 'Periféricos no encontrados'], 404);
        }

        // Log de comparación
        \Log::info('PERIFERICOS_COMPARISON_API', [
            'periferico1' => $id1,
            'periferico2' => $id2,
            'ip' => $request->ip()
        ]);

        // Aquí puedes agregar lógica de comparación personalizada
        $comparacion = "Comparación básica entre {$p1->nombre} y {$p2->nombre}.";

        return response()->json([
            'periferico1' => $p1,
            'periferico2' => $p2,
            'comparacion' => $comparacion
        ]);
    }

    // 4. Listado de categorías
    public function categorias()
    {
        return response()->json(Categoria::all());
    }

    // 5. Crear comentario/reseña
    public function comentar(Request $request, $id)
    {
        $periferico = Periferico::find($id);
        if (!$periferico) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $comentario = new Comentario();
        $comentario->periferico_id = $id;
        $comentario->usuario = $request->usuario ?? 'Anónimo';
        $comentario->texto = $request->texto;
        $comentario->save();

        return response()->json($comentario, 201);
    }
}
