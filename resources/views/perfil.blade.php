@extends('layouts.app')

@section('title', 'Mi Perfil - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5'>
    <div class='layout-content-container flex flex-col max-w-[960px] flex-1'>
        <div class='flex flex-wrap justify-between gap-3 p-4'>
            <p class='text-[#0d141c] dark:text-white tracking-light text-[32px] font-bold leading-tight min-w-72'>Mi Perfil</p>
        </div>
        
        <div class='flex p-4'>
            <div class='flex w-full flex-col gap-4 md:flex-row md:justify-between md:items-center'>
                <div class='flex gap-4'>
                    <div class='bg-center bg-no-repeat aspect-square bg-cover rounded-full min-h-32 w-32 border-2 border-gray-300 dark:border-gray-700' style='background-image: url(\"https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4F46E5&color=fff&size=128\");'></div>
                    <div class='flex flex-col justify-center'>
                        <p class='text-[#0d141c] dark:text-white text-[22px] font-bold leading-tight'>{{ Auth::user()->name }}</p>
                        <p class='text-gray-600 dark:text-gray-400 text-base font-normal leading-normal'>{{ Auth::user()->email }}</p>
                        @if(Auth::user()->userRole)
                            <span class='mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-fit bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300'>
                                {{ Auth::user()->userRole->nombre }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Botones de Gestión según Rol -->
                <div class='flex flex-col gap-2 mt-4 md:mt-0'>
                    @if(Auth::user()->isAdmin())
                        <!-- Admin: Acceso Total -->
                        <a href="{{ route('admin.users') }}" class='flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'></path>
                            </svg>
                            <span class='font-semibold'>Gestionar Usuarios</span>
                        </a>
                        <a href="{{ route('admin.perifericos') }}" class='flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'></path>
                            </svg>
                            <span class='font-semibold'>Gestionar Periféricos</span>
                        </a>
                    @elseif(Auth::user()->isSupervisor())
                        <!-- Supervisor: Ver y Editar -->
                        <a href="{{ route('supervisor.users') }}" class='flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'></path>
                            </svg>
                            <span class='font-semibold'>Ver Usuarios</span>
                        </a>
                        <a href="{{ route('supervisor.products') }}" class='flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>
                            </svg>
                            <span class='font-semibold'>Aprobar Productos</span>
                        </a>
                    @elseif(Auth::user()->isDeveloper())
                        <!-- Desarrollador: Herramientas de Dev -->
                        <a href="{{ route('developer.dashboard') }}" class='flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'></path>
                            </svg>
                            <span class='font-semibold'>Panel Developer</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        <h2 class='text-[#0d141c] dark:text-white text-[22px] font-bold leading-tight px-4 pb-3 pt-5'>
            📊 Historial de Comparaciones
            @if($comparisons->count() > 0)
                <span class='text-sm font-normal text-gray-600 dark:text-gray-400'>({{ $comparisons->count() }} comparaciones)</span>
            @endif
        </h2>
        
        @if($comparisons->count() > 0)
            <div class='px-4 py-3'>
                <div class='flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800'>
                    <table class='flex-1'>
                        <thead>
                            <tr class='bg-gray-50 dark:bg-gray-700'>
                                <th class='px-4 py-3 text-left text-[#0d141c] dark:text-white text-sm font-medium'>Producto 1</th>
                                <th class='px-4 py-3 text-center text-[#0d141c] dark:text-white text-sm font-medium'>vs</th>
                                <th class='px-4 py-3 text-left text-[#0d141c] dark:text-white text-sm font-medium'>Producto 2</th>
                                <th class='px-4 py-3 text-left text-[#0d141c] dark:text-white text-sm font-medium'>Fecha</th>
                                <th class='px-4 py-3 text-center text-gray-600 dark:text-gray-400 text-sm font-medium'>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparisons as $comparison)
                                <tr class='border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors'>
                                    <!-- Producto 1 -->
                                    <td class='px-4 py-3'>
                                        <div class='flex items-center gap-3'>
                                            @if($comparison->periferico1->hasImage())
                                                <img src="{{ $comparison->periferico1->imagen_url_completa }}" 
                                                     alt="{{ $comparison->periferico1->nombre }}"
                                                     class='w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-600'>
                                            @else
                                                <div class='w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center'>
                                                    <span class='text-gray-400 dark:text-gray-500 text-xs'>📷</span>
                                                </div>
                                            @endif
                                            <div class='flex flex-col'>
                                                <span class='text-[#0d141c] dark:text-white text-sm font-medium'>{{ Str::limit($comparison->periferico1->nombre, 30) }}</span>
                                                <span class='text-gray-500 dark:text-gray-400 text-xs'>{{ $comparison->periferico1->marca->nombre ?? 'Sin marca' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- VS -->
                                    <td class='px-2 py-3 text-center'>
                                        <span class='inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 text-xs font-bold'>VS</span>
                                    </td>
                                    
                                    <!-- Producto 2 -->
                                    <td class='px-4 py-3'>
                                        <div class='flex items-center gap-3'>
                                            @if($comparison->periferico2->hasImage())
                                                <img src="{{ $comparison->periferico2->imagen_url_completa }}" 
                                                     alt="{{ $comparison->periferico2->nombre }}"
                                                     class='w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-600'>
                                            @else
                                                <div class='w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center'>
                                                    <span class='text-gray-400 dark:text-gray-500 text-xs'>📷</span>
                                                </div>
                                            @endif
                                            <div class='flex flex-col'>
                                                <span class='text-[#0d141c] dark:text-white text-sm font-medium'>{{ Str::limit($comparison->periferico2->nombre, 30) }}</span>
                                                <span class='text-gray-500 dark:text-gray-400 text-xs'>{{ $comparison->periferico2->marca->nombre ?? 'Sin marca' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Fecha -->
                                    <td class='px-4 py-3'>
                                        <div class='flex flex-col'>
                                            <span class='text-[#0d141c] dark:text-white text-sm'>{{ $comparison->created_at->format('d/m/Y') }}</span>
                                            <span class='text-gray-500 dark:text-gray-400 text-xs'>{{ $comparison->created_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class='px-4 py-3 text-center'>
                                        <a href='/comparadora?periferico1={{ $comparison->periferico1_id }}&periferico2={{ $comparison->periferico2_id }}' 
                                           class='inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors'>
                                            <svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'></path>
                                            </svg>
                                            Comparar de nuevo
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class='px-4 py-8'>
                <div class='flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-8'>
                    <svg class='w-16 h-16 text-gray-400 dark:text-gray-600 mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path>
                    </svg>
                    <h3 class='text-xl font-semibold text-gray-900 dark:text-white mb-2'>No has realizado comparaciones</h3>
                    <p class='text-gray-600 dark:text-gray-400 text-center mb-4'>Empieza a comparar productos para ver tu historial aquí</p>
                    <a href='/comparadora' class='inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors'>
                        <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 6v6m0 0v6m0-6h6m-6 0H6'></path>
                        </svg>
                        Ir a Comparadora
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
