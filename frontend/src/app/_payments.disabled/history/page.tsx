'use client';

import { useEffect, useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { generateInvoicePDF } from '@/lib/invoice-generator';
import { Download, Search, Calendar, CreditCard, Eye } from 'lucide-react';

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

const mockPayments: Payment[] = [
  {
    id: 1,
    invoiceNumber: 'INV-000001',
    bookingId: 1,
    propertyTitle: 'Luxury Apartment in Downtown',
    amount: 2648.75,
    status: 'paid',
    paymentMethod: 'Transfer Bancar',
    date: '2024-11-01',
    dueDate: '2024-11-04',
  },
  {
    id: 2,
    invoiceNumber: 'INV-000002',
    bookingId: 2,
    propertyTitle: 'Modern Studio near University',
    amount: 1850.5,
    status: 'pending',
    paymentMethod: 'Transfer Bancar',
    date: '2024-11-05',
    dueDate: '2024-11-08',
  },
  {
    id: 3,
    invoiceNumber: 'INV-000003',
    bookingId: 3,
    propertyTitle: 'Cozy House in Suburbs',
    amount: 3420.0,
    status: 'paid',
    paymentMethod: 'Transfer Bancar',
    date: '2024-10-20',
    dueDate: '2024-10-23',
  },
  {
    id: 4,
    invoiceNumber: 'INV-000004',
    bookingId: 4,
    propertyTitle: 'Beachfront Villa',
    amount: 5670.25,
    status: 'overdue',
    paymentMethod: 'Transfer Bancar',
    date: '2024-10-15',
    dueDate: '2024-10-18',
  },
];

const statusConfig = {
  paid: { label: 'Plătit', variant: 'default' as const, color: 'bg-green-500' },
  pending: {
    label: 'În așteptare',
    variant: 'secondary' as const,
    color: 'bg-yellow-500',
  },
  overdue: {
    label: 'Întârziat',
    variant: 'destructive' as const,
    color: 'bg-red-500',
  },
  cancelled: {
    label: 'Anulat',
    variant: 'outline' as const,
    color: 'bg-gray-500',
  },
};

export default function PaymentHistoryPage() {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [filteredPayments, setFilteredPayments] = useState<Payment[]>([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Simulate API call
    setTimeout(() => {
      setPayments(mockPayments);
      setFilteredPayments(mockPayments);
      setLoading(false);
    }, 500);
  }, []);

  useEffect(() => {
    let filtered = payments;

    // Apply status filter
    if (statusFilter !== 'all') {
      filtered = filtered.filter((payment) => payment.status === statusFilter);
    }

    // Apply search filter
    if (searchQuery) {
      filtered = filtered.filter(
        (payment) =>
          payment.invoiceNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
          payment.propertyTitle.toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    setFilteredPayments(filtered);
  }, [searchQuery, statusFilter, payments]);

  const handleDownloadInvoice = (payment: Payment) => {
    generateInvoicePDF({
      invoiceNumber: payment.invoiceNumber,
  date: new Date(payment.date).toLocaleDateString('ro-RO'),
      dueDate: new Date(payment.dueDate).toLocaleDateString('ro-RO'),
      companyName: 'RentHub Platform',
      companyAddress: 'Strada Exemplu 123, București, România',
      companyEmail: 'contact@renthub.com',
      companyPhone: '+40 21 123 4567',
      customerName: 'John Doe',
      customerEmail: 'john.doe@example.com',
      customerAddress: 'Strada Client 456, București',
      items: [
        {
          description: payment.propertyTitle,
          quantity: 1,
          price: payment.amount,
          total: payment.amount,
        },
      ],
      subtotal: payment.amount,
      tax: payment.amount * 0.1,
      total: payment.amount,
      paymentMethod: payment.paymentMethod,
      bankDetails: {
        bankName: 'Banca Transilvania',
        accountName: 'RentHub SRL',
        accountNumber: 'RO49 AAAA 1B31 0075 9384 0000',
        iban: 'RO49 AAAA 1B31 0075 9384 0000',
        swift: 'BTRLRO22',
      },
      notes: `Vă mulțumim pentru plată. Pentru întrebări, contactați-ne la contact@renthub.com`,
    });
  };

  const totalPaid = payments
    .filter((p) => p.status === 'paid')
    .reduce((sum, p) => sum + p.amount, 0);
  const totalPending = payments
    .filter((p) => p.status === 'pending')
    .reduce((sum, p) => sum + p.amount, 0);
  const totalOverdue = payments
    .filter((p) => p.status === 'overdue')
    .reduce((sum, p) => sum + p.amount, 0);

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="flex justify-center items-center min-h-[400px]">
            <div className="animate-pulse">Se încarcă...</div>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2">Istoric Plăți</h1>
          <p className="text-gray-600">
            Vizualizează și descarcă facturile tale
          </p>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total Plătit</p>
                  <p className="text-2xl font-bold text-green-600">
                    {totalPaid.toFixed(2)} RON
                  </p>
                </div>
                <div className="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                  <CreditCard className="h-6 w-6 text-green-600" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">În așteptare</p>
                  <p className="text-2xl font-bold text-yellow-600">
                    {totalPending.toFixed(2)} RON
                  </p>
                </div>
                <div className="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center">
                  <Calendar className="h-6 w-6 text-yellow-600" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Întârziate</p>
                  <p className="text-2xl font-bold text-red-600">
                    {totalOverdue.toFixed(2)} RON
                  </p>
                </div>
                <div className="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center">
                  <Calendar className="h-6 w-6 text-red-600" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card className="mb-6">
          <CardContent className="pt-6">
            <div className="flex flex-col md:flex-row gap-4">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                <Input
                  placeholder="Caută după număr factură sau proprietate..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-full md:w-[200px]">
                  <SelectValue placeholder="Filtru status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toate</SelectItem>
                  <SelectItem value="paid">Plătit</SelectItem>
                  <SelectItem value="pending">În așteptare</SelectItem>
                  <SelectItem value="overdue">Întârziat</SelectItem>
                  <SelectItem value="cancelled">Anulat</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Payments List */}
        <Card>
          <CardHeader>
            <CardTitle>
              Plăți ({filteredPayments.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {filteredPayments.length === 0 ? (
              <div className="text-center py-12">
                <CreditCard className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p className="text-gray-600">Nu s-au găsit plăți</p>
              </div>
            ) : (
              <div className="space-y-4">
                {filteredPayments.map((payment) => (
                  <div
                    key={payment.id}
                    className="border rounded-lg p-4 hover:shadow-md transition-shadow"
                  >
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <h3 className="font-semibold">
                            {payment.invoiceNumber}
                          </h3>
                          <Badge variant={statusConfig[payment.status].variant}>
                            {statusConfig[payment.status].label}
                          </Badge>
                        </div>
                        <p className="text-gray-600 mb-1">
                          {payment.propertyTitle}
                        </p>
                        <div className="flex flex-wrap gap-4 text-sm text-gray-500">
                          <span>Data: {new Date(payment.date).toLocaleDateString('ro-RO')}</span>
                          <span>Scadență: {new Date(payment.dueDate).toLocaleDateString('ro-RO')}</span>
                          <span>{payment.paymentMethod}</span>
                        </div>
                      </div>
                      <div className="flex items-center gap-4">
                        <div className="text-right">
                          <p className="text-2xl font-bold">
                            {payment.amount.toFixed(2)}
                          </p>
                          <p className="text-sm text-gray-600">RON</p>
                        </div>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleDownloadInvoice(payment)}
                        >
                          <Download className="h-4 w-4 mr-2" />
                          Descarcă
                        </Button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
