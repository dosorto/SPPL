<div>
    @if($getRecord()->facturas && $getRecord()->facturas->count() > 0)
        <div class="space-y-3">
            @foreach($getRecord()->facturas as $factura)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    #{{ str_pad($factura->id, 6, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $factura->fecha_factura ? \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') : 'Sin fecha' }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-green-600 dark:text-green-400">
                                    L. {{ number_format($factura->total ?? 0, 2) }}
                                </span>
                            </div>
                            <div>
                                @php
                                    $estadoColor = match($factura->estado ?? 'Sin estado') {
                                        'pagado' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'vencido' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColor }}">
                                    {{ ucfirst($factura->estado ?? 'Sin estado') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm font-medium">No hay compras registradas</p>
                <p class="mt-1 text-sm text-gray-500">Este cliente aún no ha realizado ninguna compra.</p>
            </div>
        </div>
    @endif
</div>
