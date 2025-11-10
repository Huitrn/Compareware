@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header del Chat -->
        <div class="bg-white dark:bg-gray-800 rounded-t-2xl shadow-lg p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-8 h-8">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                    </div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">💬 Soporte Telegram</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">En línea • Responde en minutos</p>
                </div>
                <div class="hidden md:flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs font-semibold">
                        🚀 Respuesta rápida
                    </span>
                </div>
            </div>
        </div>

        <!-- Ventana de Chat -->
        <div class="bg-white dark:bg-gray-800 shadow-lg">
            <div id="chat-window" class="h-[500px] overflow-y-auto p-6 space-y-4 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
                <!-- Mensaje de bienvenida -->
                <div class="flex gap-3 animate-fade-in">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white dark:bg-gray-700 rounded-2xl rounded-tl-none shadow-md p-4 max-w-[80%]">
                            <p class="text-gray-800 dark:text-gray-200">
                                👋 ¡Hola! Bienvenido al soporte de <strong>CompareWare</strong>.
                            </p>
                            <p class="text-gray-800 dark:text-gray-200 mt-2">
                                Envíanos tu consulta y nuestro equipo te responderá directamente por Telegram. 🚀
                            </p>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 mt-1 block">Ahora</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Entrada -->
        <div class="bg-white dark:bg-gray-800 rounded-b-2xl shadow-lg p-4 border-t border-gray-200 dark:border-gray-700">
            <form id="chat-form" class="flex gap-3">
                <div class="flex-1 relative">
                    <input 
                        type="text" 
                        id="user-input" 
                        class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3 pr-12 focus:outline-none focus:border-blue-500 dark:focus:border-blue-400 text-gray-800 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors" 
                        placeholder="Escribe tu mensaje aquí..." 
                        autocomplete="off" 
                        required
                    >
                    <button 
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        title="Adjuntar archivo"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </button>
                </div>
                <button 
                    type="submit" 
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                    <span class="hidden sm:inline">Enviar</span>
                </button>
            </form>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                🔒 Tus mensajes son seguros y encriptados • Tiempo de respuesta: 5-10 minutos
            </p>
        </div>
    </div>
</div>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

@keyframes pulse-scale {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.animate-pulse-scale {
    animation: pulse-scale 2s ease-in-out infinite;
}
</style>

<script>
const chatWindow = document.getElementById('chat-window');
const chatForm = document.getElementById('chat-form');
const userInput = document.getElementById('user-input');

function appendMessage(role, text, isHtml = false) {
    const msgContainer = document.createElement('div');
    msgContainer.className = 'flex gap-3 animate-fade-in';
    
    if (role === 'user') {
        msgContainer.classList.add('flex-row-reverse');
        msgContainer.innerHTML = `
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="flex-1 flex flex-col items-end">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl rounded-tr-none shadow-md p-4 max-w-[80%]">
                    <p>${escapeHtml(text)}</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 mr-2 mt-1">Ahora</span>
            </div>
        `;
    } else {
        // Convertir saltos de línea a <br> para mejor visualización
        let displayText = text;
        if (!isHtml) {
            // Escapar HTML pero preservar formato
            displayText = escapeHtml(text)
                .replace(/\n/g, '<br>')
                .replace(/•/g, '•');
        }
        
        msgContainer.innerHTML = `
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <div class="bg-white dark:bg-gray-700 rounded-2xl rounded-tl-none shadow-md p-4 max-w-[85%]">
                    <div class="text-gray-800 dark:text-gray-200 whitespace-pre-line">${displayText}</div>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 mt-1">Ahora</span>
            </div>
        `;
    }
    
    chatWindow.appendChild(msgContainer);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

chatForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const message = userInput.value.trim();
    if (!message) return;
    
    // Mostrar mensaje del usuario
    appendMessage('user', message);
    userInput.value = '';
    
    // Mostrar indicador de "escribiendo..."
    appendMessage('bot', '<span class="italic text-gray-400">⌛ Buscando información...</span>', true);
    
    try {
        const res = await fetch("{{ route('chatbot.message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message })
        });
        
        const data = await res.json();
        
        // Eliminar mensaje de "escribiendo..."
        chatWindow.lastChild.remove();
        
        if (data.error) {
            appendMessage('bot', `❌ Error: ${data.error}`);
        } else {
            appendMessage('bot', data.reply || '✅ Mensaje enviado correctamente.');
        }
    } catch (err) {
        chatWindow.lastChild.remove();
        appendMessage('bot', '❌ Error al conectar con el servidor. Por favor, intenta nuevamente.');
        console.error('Error:', err);
    }
});

// Auto-focus en el input al cargar
document.addEventListener('DOMContentLoaded', function() {
    userInput.focus();
});
</script>
@endsection
