<div class="bg-gradient-to-r from-amber-500 to-amber-700 p-6 rounded-lg shadow-lg mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>

            <p class="text-amber-100 !important">{{ $subheading }}</p>
        </div>
        <div class="flex items-center space-x-4">
            @if (session('empresa_id'))
                <span class="text-sm font-medium bg-amber-800/50 px-3 py-1 rounded-full text-white !important">
                    Empresa ID: {{ session('empresa_id') }}
                </span>
            @endif
            
        </div>
    </div>
</div>
