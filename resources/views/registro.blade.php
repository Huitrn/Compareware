@extends('layouts.app')

@section('title', 'Registro - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5'>
    <div class='layout-content-container flex flex-col w-full max-w-[512px] py-5'>
        <h2 class='text-[#0d141c] dark:text-white tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5'>
            Regístrate en CompareWare
        </h2>
        
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
        @if (session('success'))
            <div class='bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4 w-full'>
                {{ session('success') }}
            </div>
        @endif

        <!-- Mostrar enlace a login si el email ya existe -->
        @if (session('show_login_link'))
            <div class='bg-blue-100 dark:bg-blue-900/50 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded mb-4 w-full'>
                ¿Ya tienes una cuenta? 
                <a href='{{ session('login_url') }}' class='font-medium text-blue-600 dark:text-blue-400 hover:underline'>Inicia sesión aquí</a>
            </div>
        @endif

        <form method='POST' action='{{ route('register') }}' class='flex flex-col gap-4 px-4 py-3'>
            @csrf
            <input
                type='text'
                name='name'
                placeholder='Nombre completo'
                value='{{ old('name') }}'
                class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('name') border-red-500 @enderror'
                required
            />
            <input
                type='email'
                name='email'
                placeholder='Correo electrónico'
                value='{{ old('email') }}'
                class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('email') border-red-500 @enderror'
                required
            />
            <input
                type='password'
                name='password'
                placeholder='Contraseña (mín. 8 caracteres)'
                class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('password') border-red-500 @enderror'
                required
                minlength='8'
            />
            <input
                type='password'
                name='password_confirmation'
                placeholder='Confirmar contraseña'
                class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal'
                required
                minlength='8'
            />
            <button
                type='submit'
                class='flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-4 w-full bg-[#0d80f2] dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-500 text-white text-base font-bold leading-normal tracking-[0.015em] mt-4 transition-colors'
            >
                <span class='truncate'>Crear cuenta</span>
            </button>
        </form>
        
        <div class='px-4 pt-4'>
            <p class='text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal text-center'>
                ¿Ya tienes una cuenta?
                <a href='{{ route('login') }}' class='text-blue-600 dark:text-blue-400 hover:underline font-medium'>Inicia sesión</a>
            </p>
        </div>
        
        <div class='px-4 pt-4'>
            <p class='text-[#49739c] dark:text-gray-400 text-xs text-center leading-normal'>
                Al crear una cuenta, aceptas nuestros 
                <a href='#' class='text-blue-600 dark:text-blue-400 hover:underline'>Términos de servicio</a> y 
                <a href='#' class='text-blue-600 dark:text-blue-400 hover:underline'>Política de privacidad</a>
            </p>
        </div>
    </div>
</div>
@endsection
