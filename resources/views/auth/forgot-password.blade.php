@extends('layouts.app')

@section('title', 'Recuperar Contraseña - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5 items-center'>
    <div class='layout-content-container flex flex-col w-full max-w-[512px] py-5 justify-center items-center'>
        <h2 class='text-[#0d141c] dark:text-white tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5'>
            ¿Olvidaste tu contraseña?
        </h2>
        
        <p class='text-gray-600 dark:text-gray-400 text-sm text-center px-4 pb-4'>
            No te preocupes, ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.
        </p>
        
        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class='bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4 w-full'>
                <ul class='list-disc list-inside'>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Mostrar mensaje de éxito -->
        @if (session('status'))
            <div class='bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4 w-full'>
                {{ session('status') }}
            </div>
        @endif

        <form method='POST' action='{{ route('password.email') }}' class='flex flex-col gap-4 px-4 py-3 w-full'>
            @csrf
            
            <div class='flex flex-col gap-2'>
                <label class='text-[#0d141c] dark:text-white text-sm font-medium'>
                    Correo electrónico
                </label>
                <input
                    type='email'
                    name='email'
                    placeholder='tu@email.com'
                    value='{{ old('email') }}'
                    class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('email') border-red-500 @enderror'
                    required
                    autofocus
                />
                @error('email')
                    <span class='text-red-500 dark:text-red-400 text-sm'>{{ $message }}</span>
                @enderror
            </div>

            <button
                type='submit'
                class='flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 px-6 bg-[#0d80f2] dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-500 text-slate-50 text-lg font-bold leading-normal tracking-[0.015em] mt-4 transition-colors'
            >
                <span class='truncate'>Enviar enlace de recuperación</span>
            </button>
        </form>
        
        <div class='flex items-center gap-2 pt-4'>
            <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-gray-600 dark:text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 19l-7-7m0 0l7-7m-7 7h18' />
            </svg>
            <a href='{{ route('login') }}' class='text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal hover:text-blue-600 dark:hover:text-blue-400 transition-colors'>
                Volver al inicio de sesión
            </a>
        </div>
    </div>
</div>
@endsection
