<x-filament-panels::page>
    <form wire:submit.prevent="generateReport">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
        />
    </form>

    <div class="mt-6">
        <x-filament::section>
            <x-slot name="heading">
                Rapoarte Disponibile
            </x-slot>

            <x-slot name="description">
                Selectați perioada și tipul de raport pe care doriți să îl generați
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">📊 Raport Rezervări</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Lista completă a rezervărilor cu detalii despre proprietăți, clienți și prețuri
                    </p>
                </div>

                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">💰 Raport Venituri</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Analiza veniturilor, plăți procesate și statistici financiare
                    </p>
                </div>

                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">🏠 Raport Proprietăți</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Performanța proprietăților, număr rezervări și venituri generate
                    </p>
                </div>

                <div class="p-4 border rounded-lg">
                    <h3 class="font-semibold text-lg mb-2">📈 Raport Ocupare</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Rata de ocupare a proprietăților și disponibilitate
                    </p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
