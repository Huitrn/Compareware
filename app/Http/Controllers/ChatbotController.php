<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Periferico;

class ChatbotController extends Controller
{
    /**
     * Base de conocimiento de respuestas automáticas
     */
    private $knowledgeBase = [
        // Saludos
        'saludos' => [
            'patterns' => ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'hey', 'qué tal', 'saludos'],
            'responses' => [
                '👋 ¡Hola! Bienvenido a CompareWare. ¿En qué puedo ayudarte hoy?',
                '¡Hola! 😊 Estoy aquí para ayudarte con comparaciones de productos.',
                '👋 ¡Buenas! ¿Quieres comparar algún periférico?'
            ]
        ],
        
        // Preguntas sobre productos específicos
        'productos' => [
            'patterns' => ['producto', 'periférico', 'artículo', 'auricular', 'teclado', 'mouse', 'ratón', 'monitor', 'micrófono', 'información', 'detalles', 'especificaciones'],
            'responses' => [
                "🔍 Puedo ayudarte a encontrar información sobre productos específicos.

📝 Escribe el nombre del producto que buscas, por ejemplo:
• \"SteelSeries Arctis 7\"
• \"Logitech G502\"
• \"Razer BlackShark\"

¿Qué producto te interesa?"
            ]
        ],
        
        // Preguntas sobre comparaciones
        'comparacion' => [
            'patterns' => ['comparar', 'comparación', 'diferencia', 'mejor', 'cuál elegir', 'vs', 'versus', 'entre'],
            'responses' => [
                "⚖️ ¡Nuestra especialidad es la comparación!

📊 Puedes comparar productos directamente en nuestra página principal:
1. Busca el primer producto
2. Busca el segundo producto
3. Revisa las especificaciones lado a lado

💡 ¿Necesitas ayuda para encontrar algún producto?"
            ]
        ],
        
        // Preguntas sobre precios (actualizado)
        'precios' => [
            'patterns' => ['precio', 'costo', 'cuánto cuesta', 'cuánto vale', 'barato', 'económico', 'oferta', 'descuento'],
            'responses' => [
                "💰 CompareWare es una plataforma de comparación, no vendemos productos.

📌 Lo que hacemos:
✅ Comparar especificaciones
✅ Mostrar características técnicas
✅ Ayudarte a decidir el mejor producto

🔗 Para precios y compras, te redirigimos a Amazon donde puedes ver ofertas actualizadas."
            ]
        ],
        
        // Ayuda general
        'ayuda' => [
            'patterns' => ['ayuda', 'ayúdame', 'no entiendo', 'cómo funciona', 'tutorial', 'guía'],
            'responses' => [
                "🆘 ¡Claro! Estoy aquí para ayudarte.

📌 ¿Qué puedes hacer en CompareWare?

1️⃣ Comparar productos lado a lado
2️⃣ Ver especificaciones detalladas
3️⃣ Consultar características técnicas
4️⃣ Buscar productos específicos
5️⃣ Ver enlaces a Amazon para comprar

💡 Somos una plataforma de comparación, no vendemos productos.

¿Sobre qué necesitas más información?"
            ]
        ],
        
        // Información de contacto
        'contacto' => [
            'patterns' => ['contacto', 'contactar', 'email', 'correo', 'teléfono', 'whatsapp', 'telegram'],
            'responses' => [
                "📞 Formas de contacto:

💬 Chat en vivo (estás aquí)
📧 Email: soporte@compareware.com
📱 Telegram: @CompareWareBot

⏰ Horario de atención:
Lunes a Viernes: 9:00 - 18:00
Sábados: 10:00 - 14:00

¿En qué más puedo ayudarte?"
            ]
        ],
        
        // Agradecimientos
        'agradecimiento' => [
            'patterns' => ['gracias', 'muchas gracias', 'te agradezco', 'excelente', 'perfecto', 'genial'],
            'responses' => [
                '😊 ¡De nada! Es un placer ayudarte.',
                '🙌 ¡Con gusto! Si necesitas algo más, aquí estoy.',
                '✨ ¡Encantado de ayudar! ¿Algo más en lo que pueda asistirte?'
            ]
        ],
        
        // Despedidas
        'despedida' => [
            'patterns' => ['adiós', 'hasta luego', 'chao', 'bye', 'nos vemos', 'hasta pronto'],
            'responses' => [
                '👋 ¡Hasta pronto! Vuelve cuando necesites comparar productos.',
                '😊 ¡Que tengas un excelente día! Nos vemos pronto.',
                '✨ ¡Adiós! Gracias por usar CompareWare.'
            ]
        ],
        
        // Nueva categoría: Compra/Venta
        'compra_venta' => [
            'patterns' => ['comprar', 'vender', 'venta', 'compra', 'tienda', 'carrito', 'pagar'],
            'responses' => [
                "🛒 CompareWare NO es una tienda, somos una plataforma de comparación.

✅ Lo que SÍ hacemos:
• Comparar especificaciones
• Mostrar características
• Ayudarte a elegir el mejor producto

🔗 Para comprar, te redirigimos a Amazon donde encontrarás los mejores precios y ofertas."
            ]
        ]
    ];

    /**
     * Procesar mensaje y generar respuesta automática
     */
    public function chat(Request $request)
    {
        $userMessage = $request->input('message');
        
        if (!$userMessage) {
            return response()->json(['error' => 'Mensaje vacío'], 400);
        }

        // Generar respuesta automática basada en el mensaje
        $botResponse = $this->generateAutomaticResponse($userMessage);

        // Log del intercambio
        Log::info('� Chat intercambio:', [
            'user_message' => $userMessage,
            'bot_response' => $botResponse
        ]);

        // Opcionalmente, también enviar a Telegram para registro
        $this->sendToTelegram($userMessage, $botResponse);

        return response()->json([
            'reply' => $botResponse,
            'success' => true,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Generar respuesta automática basada en palabras clave
     */
    private function generateAutomaticResponse(string $message): string
    {
        $messageLower = mb_strtolower($message);
        
        // PRIMERO: Intentar buscar un producto específico en la base de datos
        $productInfo = $this->searchProductInDatabase($message);
        if ($productInfo) {
            return $productInfo;
        }
        
        // SEGUNDO: Buscar en la base de conocimiento
        $scores = [];

        // Calcular scores para cada categoría
        foreach ($this->knowledgeBase as $category => $data) {
            $score = 0;
            foreach ($data['patterns'] as $pattern) {
                if (str_contains($messageLower, mb_strtolower($pattern))) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scores[$category] = $score;
            }
        }

        // Si encontramos coincidencias, usar la categoría con mayor score
        if (!empty($scores)) {
            arsort($scores);
            $bestCategory = array_key_first($scores);
            $responses = $this->knowledgeBase[$bestCategory]['responses'];
            return $responses[array_rand($responses)];
        }

        // Respuesta por defecto si no hay coincidencias
        return $this->getDefaultResponse($messageLower);
    }
    
    /**
     * Buscar producto en la base de datos
     */
    private function searchProductInDatabase(string $query): ?string
    {
        try {
            Log::info('🔍 Buscando producto:', ['query' => $query]);
            
            // Limpiar la query de palabras comunes
            $cleanQuery = $this->cleanSearchQuery($query);
            
            if (strlen($cleanQuery) < 3) {
                return null; // Query muy corta después de limpiar
            }
            
            Log::info('🧹 Query limpia:', ['clean_query' => $cleanQuery]);
            
            // Intentar búsqueda por nombre completo primero
            $producto = Periferico::where('nombre', 'ILIKE', "%{$cleanQuery}%")
                ->with(['marca', 'categoria'])
                ->first();
            
            if (!$producto) {
                // Intentar búsqueda por palabras individuales
                $palabras = explode(' ', $cleanQuery);
                
                Log::info('🔤 Buscando por palabras:', ['palabras' => $palabras]);
                
                if (count($palabras) >= 1) {
                    $producto = Periferico::where(function($q) use ($palabras) {
                        foreach ($palabras as $palabra) {
                            if (strlen($palabra) >= 3) { // Palabras de al menos 3 caracteres
                                $q->orWhere('nombre', 'ILIKE', "%{$palabra}%");
                            }
                        }
                    })
                    ->with(['marca', 'categoria'])
                    ->first();
                }
            }
            
            if (!$producto) {
                Log::info('❌ No se encontró producto');
                return null; // No se encontró el producto
            }
            
            Log::info('✅ Producto encontrado:', ['nombre' => $producto->nombre]);
            
            // Formatear especificaciones
            $especificaciones = '';
            if ($producto->especificaciones && is_array($producto->especificaciones)) {
                $especificaciones = "\n\n📋 Especificaciones principales:\n";
                $count = 0;
                foreach ($producto->especificaciones as $categoria => $specs) {
                    if (is_array($specs) && $count < 8) { // Limitar a 8 specs
                        foreach ($specs as $key => $value) {
                            if ($count < 8 && $value) {
                                $especificaciones .= "• {$key}: {$value}\n";
                                $count++;
                            }
                        }
                    }
                }
            }
            
            // Construir respuesta formateada (sin HTML para mejor visualización)
            $response = "🎯 Producto encontrado:\n\n";
            $response .= "📦 {$producto->nombre}\n\n";
            
            if ($producto->marca) {
                $response .= "🏷️ Marca: {$producto->marca->nombre}\n";
            }
            
            if ($producto->categoria) {
                $response .= "📂 Categoría: {$producto->categoria->nombre}\n";
            }
            
            if ($producto->descripcion) {
                $descripcion = substr($producto->descripcion, 0, 200);
                if (strlen($producto->descripcion) > 200) {
                    $descripcion .= '...';
                }
                $response .= "\n📝 Descripción:\n{$descripcion}\n";
            }
            
            $response .= $especificaciones;
            
            $response .= "\n\n💡 ¿Quieres compararlo?\n";
            $response .= "Ve a nuestra página principal y busca otro producto para comparar especificaciones lado a lado.\n";
            
            if ($producto->amazon_url) {
                $response .= "\n🔗 Ver en Amazon: {$producto->amazon_url}";
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('❌ Error al buscar producto:', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Limpiar query de búsqueda eliminando palabras comunes
     */
    private function cleanSearchQuery(string $query): string
    {
        $palabrasComunes = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas',
            'de', 'del', 'en', 'con', 'para', 'por', 'sobre',
            'información', 'info', 'detalles', 'busco', 'quiero',
            'necesito', 'me', 'gustaria', 'gustaría', 'interesa',
            'producto', 'productos', 'periférico', 'periféricos'
        ];
        
        $palabras = explode(' ', mb_strtolower($query));
        $palabrasFiltradas = array_filter($palabras, function($palabra) use ($palabrasComunes) {
            return !in_array($palabra, $palabrasComunes) && strlen($palabra) > 2;
        });
        
        return implode(' ', $palabrasFiltradas);
    }

    /**
     * Respuesta por defecto cuando no se detecta ninguna categoría
     */
    private function getDefaultResponse(string $messageLower): string
    {
        $defaultResponses = [
            "🤔 Interesante pregunta. Te puedo ayudar con:

• Buscar información de productos
• Comparar especificaciones
• Ver características técnicas

💡 Escribe el nombre de un producto específico y te mostraré su información.

¿Podrías ser más específico?",
            
            "💡 No estoy seguro de entender completamente tu pregunta, pero puedo ayudarte con:

✅ Buscar productos específicos
✅ Comparar especificaciones
✅ Ver características técnicas

🔍 Prueba escribiendo el nombre de un producto, por ejemplo:
\"SteelSeries Arctis 7\" o \"Logitech G502\"",
            
            "🎯 Déjame ayudarte mejor. ¿Tu consulta es sobre:

1️⃣ Buscar un producto específico
2️⃣ Comparar dos productos
3️⃣ Ver especificaciones técnicas
4️⃣ Información general de CompareWare

💬 ¿Puedes darme más detalles o el nombre de un producto?"
        ];

        // Si el mensaje es muy corto, dar respuesta más guiada
        if (strlen($messageLower) < 10) {
            return "¿Podrías darme más detalles? Puedo ayudarte a:

🔍 Buscar productos específicos
⚖️ Comparar especificaciones
📊 Ver características técnicas

💡 Escribe el nombre de un producto para empezar. 😊";
        }

        return $defaultResponses[array_rand($defaultResponses)];
    }

    /**
     * Enviar resumen a Telegram (opcional, para registro)
     */
    private function sendToTelegram(string $userMessage, string $botResponse): void
    {
        $telegramToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$telegramToken || !$chatId) {
            return; // No enviar si no está configurado
        }

        try {
            $telegramUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
            
            $text = "📊 <b>Nuevo Chat Automático</b>\n\n";
            $text .= "👤 <b>Usuario:</b>\n{$userMessage}\n\n";
            $text .= "🤖 <b>Bot:</b>\n" . strip_tags($botResponse) . "\n\n";
            $text .= "⏰ " . now()->format('d/m/Y H:i:s');

            Http::timeout(10)->post($telegramUrl, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ No se pudo enviar log a Telegram:', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Webhook para recibir actualizaciones de Telegram (opcional)
     */
    public function webhook(Request $request)
    {
        $update = $request->all();
        
        Log::info('📥 Webhook de Telegram recibido:', $update);

        // Aquí puedes procesar mensajes entrantes desde Telegram
        // Por ejemplo, guardar en base de datos o enviar notificaciones

        return response()->json(['ok' => true]);
    }
}
