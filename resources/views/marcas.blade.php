@extends('layouts.app')

@section('title', 'Marcas - CompareWare')

@section('content')
<div class='px-10 md:px-40 flex flex-1 justify-center py-5'>
    <div class='layout-content-container flex flex-col max-w-[960px] flex-1'>
        <div class='flex flex-wrap justify-between gap-3 p-4'>
            <p class='text-[#0d141c] dark:text-white tracking-light text-[32px] font-bold leading-tight min-w-72'>
                Marcas
                <span class='text-base font-normal text-gray-600 dark:text-gray-400'>({{ $marcas->count() }} marcas)</span>
            </p>
        </div>
        
        <div class='px-4 py-3'>
            <label class='flex flex-col min-w-40 h-12 w-full'>
                <div class='flex w-full flex-1 items-stretch rounded-lg h-full'>
                    <div
                        class='text-[#49739c] dark:text-gray-400 flex border-none bg-[#e7edf4] dark:bg-gray-800 items-center justify-center pl-4 rounded-l-lg border-r-0'
                        data-icon='MagnifyingGlass'
                        data-size='24px'
                        data-weight='regular'
                    >
                        <svg xmlns='http://www.w3.org/2000/svg' width='24px' height='24px' fill='currentColor' viewBox='0 0 256 256'>
                            <path
                                d='M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z'
                            ></path>
                        </svg>
                    </div>
                    <input
                        id='search-brands'
                        placeholder='Buscar marca'
                        class='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border-none bg-[#e7edf4] dark:bg-gray-800 focus:border-none h-full placeholder:text-[#49739c] dark:placeholder:text-gray-400 px-4 rounded-l-none border-l-0 pl-2 text-base font-normal leading-normal'
                        value=''
                    />
                </div>
            </label>
        </div>
        
        @if($marcas->count() > 0)
            <div class='flex flex-col gap-4 p-4'>
                @foreach($marcas as $marca)
                    <div class='brand-item border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden transition-all hover:border-purple-500 dark:hover:border-purple-400' data-brand-name='{{ strtolower($marca->nombre) }}'>
                        <!-- Header de la marca (clickeable) -->
                        <button 
                            onclick='toggleBrand({{ $marca->id }})'
                            class='w-full flex items-center justify-between p-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors'
                        >
                            <div class='flex items-center gap-4'>
                                <!-- Inicial de la marca -->
                                <div class='w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-lg flex items-center justify-center text-white text-2xl font-bold'>
                                    {{ strtoupper(substr($marca->nombre, 0, 1)) }}
                                </div>
                                
                                <!-- Nombre y contador -->
                                <div class='text-left'>
                                    <p class='text-[#0d141c] dark:text-white text-lg font-bold'>{{ $marca->nombre }}</p>
                                    <p class='text-gray-600 dark:text-gray-400 text-sm'>
                                        {{ $marca->perifericos_count }} {{ $marca->perifericos_count == 1 ? 'producto' : 'productos' }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Ícono de flecha -->
                            <svg 
                                id='arrow-{{ $marca->id }}'
                                xmlns='http://www.w3.org/2000/svg' 
                                class='h-6 w-6 text-gray-600 dark:text-gray-400 transition-transform duration-300' 
                                fill='none' 
                                viewBox='0 0 24 24' 
                                stroke='currentColor'
                            >
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7' />
                            </svg>
                        </button>
                        
                        <!-- Lista de productos (desplegable) -->
                        <div id='products-{{ $marca->id }}' class='hidden border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900'>
                            @if($marca->perifericos->count() > 0)
                                <div class='grid grid-cols-1 md:grid-cols-2 gap-3 p-4'>
                                    @foreach($marca->perifericos as $producto)
                                        <div class='flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition-shadow'>
                                            <!-- Thumbnail del producto -->
                                            <div class='w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden'>
                                                @if($producto->imagen_url)
                                                    <img src='{{ $producto->thumbnail_url_completa }}' alt='{{ $producto->nombre }}' class='w-full h-full object-cover'>
                                                @else
                                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-8 w-8 text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' />
                                                    </svg>
                                                @endif
                                            </div>
                                            
                                            <!-- Info del producto -->
                                            <div class='flex-1 min-w-0'>
                                                <p class='text-[#0d141c] dark:text-white text-sm font-medium truncate'>{{ $producto->nombre }}</p>
                                                <p class='text-gray-600 dark:text-gray-400 text-xs'>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</p>
                                                @if($producto->precio)
                                                    <p class='text-purple-600 dark:text-purple-400 text-sm font-bold'>${{ number_format($producto->precio, 2) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class='p-8 text-center'>
                                    <p class='text-gray-600 dark:text-gray-400'>No hay productos disponibles para esta marca</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class='flex flex-col items-center justify-center py-16 px-4'>
                <svg xmlns='http://www.w3.org/2000/svg' class='h-24 w-24 text-gray-400 dark:text-gray-600 mb-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' />
                </svg>
                <p class='text-[#0d141c] dark:text-white text-xl font-medium mb-2'>No hay marcas disponibles</p>
                <p class='text-gray-600 dark:text-gray-400 text-center'>Aún no se han agregado marcas al sistema</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Búsqueda en tiempo real
    document.getElementById('search-brands').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const brandItems = document.querySelectorAll('.brand-item');
        
        brandItems.forEach(item => {
            const brandName = item.getAttribute('data-brand-name');
            if (brandName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Toggle para expandir/colapsar productos
    function toggleBrand(brandId) {
        const productsDiv = document.getElementById(`products-${brandId}`);
        const arrow = document.getElementById(`arrow-${brandId}`);
        
        if (productsDiv.classList.contains('hidden')) {
            productsDiv.classList.remove('hidden');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            productsDiv.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    }
</script>
@endsection