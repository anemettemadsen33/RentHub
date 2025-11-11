# Payment System - Transfer Bancar

Sistem complet de plăți prin transfer bancar cu generare facturi PDF.

## 📋 Prezentare Generală

Sistemul de plăți este configurat exclusiv pentru transfer bancar, fără integrare Stripe sau alte procesatoare de plăți online. Utilizatorii primesc facturi PDF cu detaliile bancare pentru plată.

## 🎯 Funcționalități

### 1. Pagina de Checkout (`/bookings/[id]/payment`)

**Caracteristici:**
- Afișare detalii completă rezervare
- Informații proprietate (titlu, adresă, date check-in/check-out)
- Detalii bancare pentru transfer
- Rezumat costuri (subtotal, taxe de curățenie, serviciu, TVA)
- Buton confirmare rezervare cu descărcare automată factură PDF

**Detalii Transfer Bancar:**
```
Bancă: Banca Transilvania
Beneficiar: RentHub SRL
IBAN: RO49 AAAA 1B31 0075 9384 0000
SWIFT: BTRLRO22
Descriere: INV-XXXXXX (număr factură)
```

**Fluxul de Plată:**
1. Utilizatorul revizuiește detaliile rezervării
2. Verifică detaliile bancare
3. Apasă "Confirmă și Descarcă Factură"
4. Factura PDF se descarcă automat
5. Redirect către `/payments/history`
6. Utilizatorul efectuează transferul bancar folosind datele din factură

### 2. Istoric Plăți (`/payments/history`)

**Caracteristici:**
- Lista toate plățile (plătite, în așteptare, întârziate, anulate)
- Căutare după număr factură sau nume proprietate
- Filtrare după status plată
- Descărcare factură PDF pentru fiecare plată
- Cards rezumat cu total plătit, în așteptare, întârziat

**Statusuri Plată:**
- 🟢 **Plătit** - Transferul a fost primit și confirmat
- 🟡 **În așteptare** - Așteaptă plata în termen de 3 zile
- 🔴 **Întârziat** - Termenul de plată a expirat
- ⚪ **Anulat** - Rezervarea a fost anulată

**Funcții:**
- Search bar pentru căutare rapidă
- Dropdown pentru filtrare status
- Butoane descărcare pentru fiecare factură
- Vizualizare detalii: dată emisie, scadență, metodă plată

### 3. Generator Facturi PDF (`/lib/invoice-generator.ts`)

**Două Funcții Principale:**

#### `generateInvoicePDF(data: InvoiceData)`
Generează și descarcă factura PDF:
```typescript
generateInvoicePDF({
  invoiceNumber: 'INV-000001',
  date: '07.11.2024',
  dueDate: '10.11.2024',
  companyName: 'RentHub Platform',
  companyAddress: 'Strada Exemplu 123, București',
  companyEmail: 'contact@renthub.com',
  companyPhone: '+40 21 123 4567',
  customerName: 'John Doe',
  customerEmail: 'john@example.com',
  customerAddress: 'Strada Client 456',
  items: [
    {
      description: 'Luxury Apartment - 5 nopți',
      quantity: 5,
      price: 450,
      total: 2250
    }
  ],
  subtotal: 2250,
  tax: 225,
  total: 2475,
  paymentMethod: 'Transfer Bancar',
  bankDetails: {
    bankName: 'Banca Transilvania',
    accountName: 'RentHub SRL',
    accountNumber: 'RO49 AAAA 1B31 0075 9384 0000',
    iban: 'RO49 AAAA 1B31 0075 9384 0000',
    swift: 'BTRLRO22'
  },
  notes: 'Plata în termen de 3 zile lucrătoare'
});
```

#### `previewInvoicePDF(data: InvoiceData)`
Deschide factura într-o fereastră nouă pentru preview (fără descărcare).

**Layout Factură:**
- Header albastru cu logo "INVOICE"
- Informații companie (stânga sus)
- Detalii factură (dreapta sus): număr, dată, scadență
- Secțiune "Bill To" cu detalii client
- Tabel items: Descriere | Cantitate | Preț | Total
- Subtotal, TVA, Total
- Metodă de plată
- **Detalii bancare** (bancă, cont, IBAN, SWIFT)
- Note și instrucțiuni plată
- Footer: "Thank you for your business!"

## 📊 Interfețe TypeScript

### InvoiceData
```typescript
interface InvoiceData {
  invoiceNumber: string;
  date: string;
  dueDate?: string;
  
  companyName: string;
  companyAddress: string;
  companyEmail: string;
  companyPhone: string;
  
  customerName: string;
  customerEmail: string;
  customerAddress?: string;
  
  items: {
    description: string;
    quantity: number;
    price: number;
    total: number;
  }[];
  
  subtotal: number;
  tax?: number;
  total: number;
  
  paymentMethod: string;
  bankDetails?: {
    bankName: string;
    accountName: string;
    accountNumber: string;
    iban: string;
    swift: string;
  };
  
  notes?: string;
}
```

### Payment
```typescript
interface Payment {
  id: number;
  invoiceNumber: string;
  bookingId: number;
  propertyTitle: string;
  amount: number;
  status: 'paid' | 'pending' | 'overdue' | 'cancelled';
  paymentMethod: string;
  date: string;
  dueDate: string;
}
```

## 🎨 Componente UI

### Componente Necesare
- `Card`, `CardContent`, `CardHeader`, `CardTitle`
- `Button`
- `Badge` - pentru statusuri plată
- `Input` - pentru search
- `Select` - pentru filtre
- Icons: `Download`, `Calendar`, `CreditCard`, `Search`, `CheckCircle`, `AlertCircle`

## 🔄 Fluxul Complet

```
1. Utilizator face rezervare
   ↓
2. Redirect la /bookings/[id]/payment
   ↓
3. Revizuiește detalii și detalii bancare
   ↓
4. Apasă "Confirmă și Descarcă Factură"
   ↓
5. Factura PDF se descarcă automat
   ↓
6. Redirect la /payments/history
   ↓
7. Utilizator efectuează transfer bancar
   ↓
8. Admin confirmă plata (manual/webhook bancar)
   ↓
9. Status rezervare: pending → paid
```

## 💡 Recomandări pentru Producție

### Backend Integration
1. **Create Payment Record**
   ```php
   POST /api/v1/payments
   {
     "booking_id": 1,
     "amount": 2648.75,
     "payment_method": "bank_transfer",
     "status": "pending"
   }
   ```

2. **Update Payment Status**
   ```php
   PUT /api/v1/payments/{id}/status
   {
     "status": "paid",
     "transaction_id": "BT123456789"
   }
   ```

3. **Get Payment History**
   ```php
   GET /api/v1/payments?user_id={id}&status=all
   ```

### Webhook Bancar (opțional)
Pentru confirmare automată plăți:
```php
POST /api/v1/webhooks/bank-transfer
{
  "transaction_id": "BT123456789",
  "reference": "INV-000001",
  "amount": 2648.75,
  "date": "2024-11-07"
}
```

### Email Notifications
1. **Rezervare confirmată** - trimite factura PDF ca attachment
2. **Reminder plată** - după 2 zile dacă status = pending
3. **Plată primită** - confirmează plată și rezervare
4. **Plată întârziată** - după trecerea termenului

### Database Schema
```sql
CREATE TABLE payments (
  id BIGINT PRIMARY KEY,
  booking_id BIGINT,
  invoice_number VARCHAR(20) UNIQUE,
  amount DECIMAL(10,2),
  status ENUM('pending','paid','overdue','cancelled'),
  payment_method VARCHAR(50),
  due_date DATE,
  paid_at TIMESTAMP NULL,
  transaction_id VARCHAR(100) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

## 🔒 Securitate

1. **Validare Server-Side** - toate datele validate pe backend
2. **Auth Required** - doar utilizatori autentificați pot accesa plățile
3. **Owner Verification** - utilizatorii văd doar propriile plăți
4. **Invoice Download** - verifică ownership înainte de generare PDF

## 📱 Mobile Responsive

Toate paginile sunt complet responsive:
- Grid layout adaptiv (1 col mobile, 2-3 col desktop)
- Buttons full-width pe mobile
- Cards stacked vertical pe ecrane mici
- Touch-friendly buttons și inputs

## 🎯 Next Steps

1. **Backend API** - implementează endpoints pentru plăți
2. **Email Templates** - crează template-uri pentru notificări
3. **Admin Panel** - interfață pentru confirmare manuală plăți
4. **Webhook Integration** - conectează cu API-ul băncii
5. **Reports** - rapoarte financiare și reconciliere

## 📞 Support

Pentru întrebări legate de plăți:
- Email: contact@renthub.com
- Telefon: +40 21 123 4567
- Program: Luni-Vineri 9:00-18:00
