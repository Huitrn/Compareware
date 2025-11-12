@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">📋 Logs del Sistema</h1>
                    <p class="text-amber-100">Últimas 100 líneas del archivo de logs</p>
                </div>
                <a href="{{ route('developer.dashboard') }}" class="px-4 py-2 bg-white text-amber-600 rounded-lg hover:bg-amber-50 transition font-semibold">
                    ← Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- Logs Content -->
        <div class="bg-white rounded-lg shadow p-6">
            @if(count($logs) > 0)
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-green-400 text-xs font-mono">@foreach($logs as $log){{ $log }}
@endforeach</pre>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">No hay logs disponibles</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
