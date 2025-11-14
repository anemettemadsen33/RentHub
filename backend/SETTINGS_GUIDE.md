# Setări Aplicație - Ghid de Utilizare

## Acces

Pentru a accesa pagina de setări, navighează la:
```
http://localhost:8000/admin/settings
```

Trebuie să fii autentificat ca administrator în panoul Filament.

## Funcționalități

Pagina de setări este organizată în 5 tab-uri principale:

### 1. Frontend
- **Setări Generale**
  - Nume Site: Numele afișat al aplicației
  - Descriere Site: Descrierea scurtă a site-ului
  - URL Frontend: URL-ul aplicației React (http://localhost:3000)
  - Elemente per Pagină: Numărul de elemente afișate în listări
  - Activează Înregistrări: Permite utilizatorilor să se înregistreze
  - Mod Mentenanță: Pune site-ul în modul de mentenanță

- **Autentificare Socială**
  - Activează Login cu Google
  - Activează Login cu Facebook

### 2. Companie
- Nume Companie
- Email Companie
- Telefon
- Adresă

### 3. Email
Configurează setările SMTP pentru trimiterea de emailuri:
- Driver Email (SMTP, Sendmail, Mailgun, Amazon SES, Log)
- Host SMTP (ex: smtp.gmail.com)
- Port SMTP (ex: 587)
- Username SMTP
- Parolă SMTP (ascunsă, cu opțiune de afișare)
- Criptare (TLS, SSL, Niciuna)
- Email Expeditor
- Nume Expeditor

### 4. Plăți
Configurare Stripe pentru procesarea plăților:
- Activează Stripe
- Cheie Publică Stripe
- Cheie Secretă Stripe

### 5. SMS
Configurare Twilio pentru trimiterea de SMS-uri:
- Activează Twilio
- Twilio SID
- Twilio Auth Token
- Număr Telefon Twilio

## Utilizare

1. Navighează la tab-ul dorit
2. Completează sau modifică setările
3. Apasă butonul "Salvează Setări" din partea de jos a formularului
4. Vei primi o notificare de confirmare când setările sunt salvate cu succes

## Acces Programatic

Poți accesa setările din cod folosind modelul `Setting` sau funcțiile helper:

### Folosind Modelul Setting
```php
use App\Models\Setting;

// Obține o setare
$frontendUrl = Setting::get('frontend_url', 'http://localhost:3000');

// Setează o setare
Setting::set('site_name', 'RentHub România', 'frontend', 'string');
```

### Folosind Funcțiile Helper (Recomandat)
```php
// Obține o setare
$frontendUrl = setting('frontend_url', 'http://localhost:3000');
$siteName = setting('site_name');

// Setează o setare
set_setting('site_name', 'RentHub România', 'frontend', 'string');

// Acces la toate setările
$allSettings = setting()->all();
```

### Exemple Practice
```php
// În controllers
class PropertyController extends Controller
{
    public function index()
    {
        $itemsPerPage = setting('items_per_page', 12);
        $properties = Property::paginate($itemsPerPage);
        
        return view('properties.index', compact('properties'));
    }
}

// În config
'frontend_url' => setting('frontend_url', env('FRONTEND_URL', 'http://localhost:3000')),

// În views (Blade)
<title>{{ setting('site_name', 'RentHub') }}</title>
<meta name="description" content="{{ setting('site_description') }}">

// Verificare maintenance mode
if (setting('maintenance_mode') === '1') {
    abort(503, 'Site în mentenanță');
}
```


## Note Importante

- Setările sunt stocate în baza de date și sunt cache-uite pentru performanță
- Cache-ul este șters automat când salvezi setările
- Parolele și cheile secrete sunt ascunse implicit, dar pot fi afișate folosind butonul de "reveal"
- Unele câmpuri devin vizibile doar când activezi funcționalitatea asociată (ex: câmpurile Stripe apar doar când activezi Stripe)

## Rezolvare Probleme

### Eroare "Unable to locate a class or view for component"
Rulează următoarele comenzi:
```bash
php artisan filament:assets
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Setările nu se salvează
Verifică că tabelul `settings` există în baza de date și că are structura corectă:
```bash
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('settings'), JSON_PRETTY_PRINT);"
```

### Seed-uire setări implicite
Pentru a popula setările implicite:
```bash
php artisan db:seed --class=SettingsSeeder
```
