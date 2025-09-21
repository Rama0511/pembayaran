<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Menu Penagihan') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 flex flex-col gap-6">
                    <div>
                        <h3 class="text-lg font-bold mb-2 sm:mb-4">Pelanggan Telat</h3>
                        <x-billing-table :customers="$late" :invoicesThisMonth="$invoicesThisMonth" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2 sm:mb-4">Pelanggan Hampir Telat (H-7)</h3>
                        <x-billing-table :customers="$almostLate" :invoicesThisMonth="$invoicesThisMonth" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2 sm:mb-4">Pelanggan Lainnya</h3>
                        <x-billing-table :customers="$others" :invoicesThisMonth="$invoicesThisMonth" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
