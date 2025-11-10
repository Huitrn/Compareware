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
            </div>
        </div>
        
        <h2 class='text-[#0d141c] dark:text-white text-[22px] font-bold leading-tight px-4 pb-3 pt-5'>Favoritos</h2>
        <div class='px-4 py-3'>
            <div class='flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800'>
                <table class='flex-1'>
                    <thead>
                        <tr class='bg-gray-50 dark:bg-gray-700'>
                            <th class='px-4 py-3 text-left text-[#0d141c] dark:text-white text-sm font-medium'>Producto</th>
                            <th class='px-4 py-3 text-left text-[#0d141c] dark:text-white text-sm font-medium'>Descripción</th>
                            <th class='px-4 py-3 text-left text-gray-600 dark:text-gray-400 text-sm font-medium'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class='border-t border-gray-200 dark:border-gray-700'>
                            <td class='h-[72px] px-4 py-2 text-[#0d141c] dark:text-white text-sm'>Teclado Mecánico</td>
                            <td class='h-[72px] px-4 py-2 text-gray-600 dark:text-gray-400 text-sm'>Teclado mecánico con interruptores azules</td>
                            <td class='h-[72px] px-4 py-2 text-sm'>
                                <button class='text-blue-600 dark:text-blue-400 hover:underline'>Ver detalles</button>
                                <span class='text-gray-400'> | </span>
                                <button class='text-red-600 dark:text-red-400 hover:underline'>Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class='flex px-4 py-3 justify-start'>
            <form method='POST' action='{{ route('logout') }}' class='inline'>
                @csrf
                <button type='submit' class='flex cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-600 text-white text-sm font-bold transition-colors'>
                    <span class='truncate'>Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
