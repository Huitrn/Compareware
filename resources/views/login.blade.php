@extends('layouts.app')

@section('title', 'Iniciar Sesión - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5 items-center'>
    <div class='layout-content-container flex flex-col w-full max-w-[512px] py-5 justify-center items-center'>
        <h2 class='text-[#0d141c] dark:text-white tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5'>
            Iniciar sesión en CompareWare
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

        <!-- Mostrar enlace a registro si el usuario no existe -->
        @if (session('show_register_link'))
            <div class='bg-blue-100 dark:bg-blue-900/50 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded mb-4 w-full'>
                ¿No tienes una cuenta? 
                <a href='{{ session('register_url') }}' class='font-medium text-blue-600 dark:text-blue-400 hover:underline'>Regístrate aquí</a>
            </div>
        @endif

        <form method='POST' action='{{ route('login') }}' class='flex flex-col gap-4 px-4 py-3 w-full items-center'>
            @csrf
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
                placeholder='Contraseña'
                class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#0d80f2] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('password') border-red-500 @enderror'
                required
            />

            <div class='flex items-center gap-4 bg-slate-50 dark:bg-gray-900 px-4 min-h-14 justify-between w-full'>
                <p class='text-[#0d141c] dark:text-white text-base font-normal leading-normal flex-1 truncate'>Recordarme</p>
                <div class='shrink-0'>
                    <label class='relative flex h-[31px] w-[51px] cursor-pointer items-center rounded-full border-none bg-[#e7edf4] dark:bg-gray-700 p-0.5 has-[:checked]:justify-end has-[:checked]:bg-[#0d80f2]'>
                        <div class='h-full w-[27px] rounded-full bg-white dark:bg-gray-300' style='box-shadow: rgba(0, 0, 0, 0.15) 0px 3px 8px, rgba(0, 0, 0, 0.06) 0px 3px 1px;'></div>
                        <input type='checkbox' name='remember' class='invisible absolute' />
                    </label>
                </div>
            </div>

            <button
                type='submit'
                class='flex min-w-[180px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 px-6 flex-1 bg-[#0d80f2] dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-500 text-slate-50 text-lg font-bold leading-normal tracking-[0.015em] mt-8 transition-colors w-full'
            >
                <span class='truncate'>Iniciar sesión</span>
            </button>
        </form>
        
        <a href='{{ route('password.request') }}' class='text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal pb-3 pt-1 px-4 underline cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors'>
            ¿Olvidaste tu contraseña?
        </a>
        <p class='text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal pb-3 pt-1 px-4 text-center'>
            ¿No tienes una cuenta?
            <a href='{{ route('register') }}' class='text-blue-600 dark:text-blue-400 hover:underline font-medium'>Regístrate</a>
        </p>
    </div>
</div>
@endsection
