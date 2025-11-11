@extends('layouts.app')

@section('title', 'Editar Perfil - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5'>
    <div class='layout-content-container flex flex-col max-w-[960px] flex-1'>
        <div class='flex flex-wrap justify-between gap-3 p-4'>
            <p class='text-[#0d141c] dark:text-white tracking-light text-[32px] font-bold leading-tight min-w-72'>Editar Perfil</p>
        </div>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class='mx-4 mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg'>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class='mx-4 mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg'>
                <ul class='list-disc list-inside'>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de actualización de perfil -->
        <form method='POST' action='{{ route('perfil.actualizar') }}'>
            @csrf
            
            <div class='flex w-full bg-slate-50 dark:bg-gray-900 @container p-4'>
                <div class='w-full gap-1 overflow-hidden @[480px]:gap-2 flex'>
                    <div
                        class='bg-center bg-no-repeat bg-cover rounded-full w-32 h-32 border-2 border-gray-300 dark:border-gray-700'
                        style='background-image: url("https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4F46E5&color=fff&size=128");'
                    ></div>
                </div>
            </div>
            
            <div class='flex px-4 py-3 justify-start'>
                <button
                    type='button'
                    class='flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#e7edf4] dark:bg-gray-700 text-[#0d141c] dark:text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors'
                    onclick='alert("Funcionalidad de cambio de foto próximamente")'
                >
                    <span class='truncate'>Cambiar foto de perfil</span>
                </button>
            </div>
            
            <div class='flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3'>
                <label class='flex flex-col min-w-40 flex-1'>
                    <p class='text-[#0d141c] dark:text-white text-base font-medium leading-normal pb-2'>Nombre de usuario</p>
                    <input
                        type='text'
                        name='name'
                        class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-purple-500 dark:focus:border-purple-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('name') border-red-500 @enderror'
                        value='{{ old('name', Auth::user()->name) }}'
                        required
                    />
                    @error('name')
                        <span class='text-red-500 dark:text-red-400 text-sm mt-1'>{{ $message }}</span>
                    @enderror
                </label>
            </div>
            
            <div class='flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3'>
                <label class='flex flex-col min-w-40 flex-1'>
                    <p class='text-[#0d141c] dark:text-white text-base font-medium leading-normal pb-2'>Correo electrónico</p>
                    <input
                        type='email'
                        name='email'
                        class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-purple-500 dark:focus:border-purple-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('email') border-red-500 @enderror'
                        value='{{ old('email', Auth::user()->email) }}'
                        required
                    />
                    @error('email')
                        <span class='text-red-500 dark:text-red-400 text-sm mt-1'>{{ $message }}</span>
                    @enderror
                </label>
            </div>
            
            <div class='flex px-4 py-3 justify-end gap-3'>
                <a
                    href='{{ route('perfil') }}'
                    class='flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-gray-200 dark:bg-gray-700 text-[#0d141c] dark:text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors'
                >
                    <span class='truncate'>Cancelar</span>
                </a>
                <button
                    type='submit'
                    class='flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-purple-600 dark:bg-purple-700 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-purple-700 dark:hover:bg-purple-600 transition-colors'
                >
                    <span class='truncate'>Guardar cambios</span>
                </button>
            </div>
        </form>

        <!-- Sección de cambio de contraseña -->
        <div class='border-t border-gray-300 dark:border-gray-700 mt-8 pt-8'>
            <h3 class='text-[#0d141c] dark:text-white text-[22px] font-bold leading-tight px-4 pb-3'>
                🔒 Cambiar Contraseña
            </h3>
            
            <form method='POST' action='{{ route('perfil.password') }}'>
                @csrf
                
                <div class='flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3'>
                    <label class='flex flex-col min-w-40 flex-1'>
                        <p class='text-[#0d141c] dark:text-white text-base font-medium leading-normal pb-2'>Contraseña actual</p>
                        <input
                            type='password'
                            name='current_password'
                            class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-purple-500 dark:focus:border-purple-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('current_password') border-red-500 @enderror'
                            placeholder='Ingresa tu contraseña actual'
                        />
                        @error('current_password')
                            <span class='text-red-500 dark:text-red-400 text-sm mt-1'>{{ $message }}</span>
                        @enderror
                    </label>
                </div>
                
                <div class='flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3'>
                    <label class='flex flex-col min-w-40 flex-1'>
                        <p class='text-[#0d141c] dark:text-white text-base font-medium leading-normal pb-2'>Nueva contraseña</p>
                        <input
                            type='password'
                            name='password'
                            class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-purple-500 dark:focus:border-purple-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('password') border-red-500 @enderror'
                            placeholder='Mínimo 8 caracteres, mayúsculas y números'
                        />
                        @error('password')
                            <span class='text-red-500 dark:text-red-400 text-sm mt-1'>{{ $message }}</span>
                        @enderror
                    </label>
                </div>
                
                <div class='flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3'>
                    <label class='flex flex-col min-w-40 flex-1'>
                        <p class='text-[#0d141c] dark:text-white text-base font-medium leading-normal pb-2'>Confirmar nueva contraseña</p>
                        <input
                            type='password'
                            name='password_confirmation'
                            class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-purple-500 dark:focus:border-purple-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal'
                            placeholder='Repite la nueva contraseña'
                        />
                    </label>
                </div>
                
                <div class='flex px-4 py-3 justify-start'>
                    <button
                        type='submit'
                        class='flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-orange-600 dark:bg-orange-700 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-orange-700 dark:hover:bg-orange-600 transition-colors'
                    >
                        <span class='truncate'>Actualizar Contraseña</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
