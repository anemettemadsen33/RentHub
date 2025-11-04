<?php

namespace Database\Seeders;

use App\Services\TranslationService;
use Illuminate\Database\Seeder;

class DefaultTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(TranslationService::class);

        // English translations
        $enCommon = [
            'welcome' => 'Welcome',
            'home' => 'Home',
            'search' => 'Search',
            'properties' => 'Properties',
            'bookings' => 'Bookings',
            'messages' => 'Messages',
            'profile' => 'Profile',
            'logout' => 'Logout',
            'login' => 'Login',
            'register' => 'Register',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete',
            'edit' => 'Edit',
            'view' => 'View',
            'back' => 'Back',
            'next' => 'Next',
            'previous' => 'Previous',
            'loading' => 'Loading...',
        ];

        $enProperties = [
            'title' => 'Property Title',
            'description' => 'Description',
            'price_per_night' => 'Price per night',
            'bedrooms' => 'Bedrooms',
            'bathrooms' => 'Bathrooms',
            'guests' => 'Guests',
            'amenities' => 'Amenities',
            'location' => 'Location',
            'availability' => 'Availability',
            'book_now' => 'Book Now',
        ];

        // Romanian translations
        $roCommon = [
            'welcome' => 'Bine ai venit',
            'home' => 'Acasă',
            'search' => 'Caută',
            'properties' => 'Proprietăți',
            'bookings' => 'Rezervări',
            'messages' => 'Mesaje',
            'profile' => 'Profil',
            'logout' => 'Deconectare',
            'login' => 'Autentificare',
            'register' => 'Înregistrare',
            'save' => 'Salvează',
            'cancel' => 'Anulează',
            'delete' => 'Șterge',
            'edit' => 'Editează',
            'view' => 'Vizualizează',
            'back' => 'Înapoi',
            'next' => 'Următorul',
            'previous' => 'Anterior',
            'loading' => 'Se încarcă...',
        ];

        $roProperties = [
            'title' => 'Titlu proprietate',
            'description' => 'Descriere',
            'price_per_night' => 'Preț pe noapte',
            'bedrooms' => 'Dormitoare',
            'bathrooms' => 'Băi',
            'guests' => 'Oaspeți',
            'amenities' => 'Facilități',
            'location' => 'Locație',
            'availability' => 'Disponibilitate',
            'book_now' => 'Rezervă acum',
        ];

        // Spanish translations
        $esCommon = [
            'welcome' => 'Bienvenido',
            'home' => 'Inicio',
            'search' => 'Buscar',
            'properties' => 'Propiedades',
            'bookings' => 'Reservas',
            'messages' => 'Mensajes',
            'profile' => 'Perfil',
            'logout' => 'Cerrar sesión',
            'login' => 'Iniciar sesión',
            'register' => 'Registrarse',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'delete' => 'Eliminar',
            'edit' => 'Editar',
            'view' => 'Ver',
            'back' => 'Volver',
            'next' => 'Siguiente',
            'previous' => 'Anterior',
            'loading' => 'Cargando...',
        ];

        // Import translations
        $service->importTranslations('en', $enCommon, 'common');
        $service->importTranslations('en', $enProperties, 'properties');

        $service->importTranslations('ro', $roCommon, 'common');
        $service->importTranslations('ro', $roProperties, 'properties');

        $service->importTranslations('es', $esCommon, 'common');

        $this->command->info('✅ Default translations seeded successfully!');
    }
}
