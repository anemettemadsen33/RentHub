import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Integration, SyncResult } from '@/services/integration-service';
import { CheckCircle, XCircle, AlertCircle, RefreshCw, Settings, ExternalLink } from 'lucide-react';
import { toast } from 'sonner';

interface IntegrationCardProps {
  integration: Integration;
  onConnect: (platform: string) => void;
  onDisconnect: (id: string) => void;
  onSync: (id: string) => Promise<SyncResult>;
  syncing: boolean;
}

const platformConfig = {
  airbnb: {
    name: 'Airbnb',
    color: 'from-red-500 to-pink-500',
    icon: (
      <svg className="h-16 w-16 text-white" viewBox="0 0 1000 1000" fill="currentColor">
        <path d="M499.3 736.7c-51-64-81-120.1-91-168.1-10-39-6-70 11-93 18-27 45-40 80-40s62 13 80 40c17 23 21 54 11 93-11 49-41 105-91 168.1zm362.2 43c-7 47-39 86-83 105-85 37-169.1-22-241.1-102 119.1-149.1 141.1-265.1 90-340.2-30-43-73-64-128.1-64-111 0-172.1 94-148.1 203.1 14 59 51 126.1 110 201.1-37 41-72 70-103 88-24 13-47 21-69 23-101 15-180.1-83-144.1-184.1 5-13 15-37 32-74l1-2c55-120.1 122.1-256.1 199.1-407.2l2-5 22-42c17-31 24-45 51-62 13-8 29-12 47-12 36 0 64 21 76 38 6 9 13 21 22 36l21 41 3 6c77 151.1 144.1 287.1 199.1 407.2l1 1 20 46 12 29c9.2 23.1 11.2 46.1 8.2 70.1z"/>
      </svg>
    ),
    features: [
      'Two-way calendar synchronization',
      'Automatic pricing and availability updates',
      'Unified messaging and booking management',
    ],
  },
  booking: {
    name: 'Booking.com',
    color: 'from-blue-600 to-blue-400',
    icon: <span className="text-2xl font-bold text-white">B.com</span>,
    features: [
      'Real-time inventory synchronization',
      'Dynamic rate management',
      'Booking confirmation automation',
    ],
  },
  vrbo: {
    name: 'Vrbo (Vacation Rentals by Owner)',
    color: 'from-blue-700 to-blue-500',
    icon: <span className="text-2xl font-bold text-white">Vrbo</span>,
    features: [
      'Automated listing distribution',
      'Payment processing integration',
      'Review management and sync',
    ],
  },
};

export function IntegrationCard({ integration, onConnect, onDisconnect, onSync, syncing }: IntegrationCardProps) {
  const [showSettings, setShowSettings] = useState(false);
  const [localSyncing, setLocalSyncing] = useState(false);

  const config = platformConfig[integration.type as keyof typeof platformConfig];
  if (!config) return null;

  const handleSync = async () => {
    setLocalSyncing(true);
    try {
      const result = await onSync(integration.id);
      if (result.success) {
        toast.success(`Synced ${result.properties_synced} properties and ${result.bookings_synced} bookings`);
      } else {
        toast.error(`Sync failed: ${result.message}`);
      }
    } catch (error) {
      toast.error('Sync failed');
    } finally {
      setLocalSyncing(false);
    }
  };

  const getStatusBadge = () => {
    switch (integration.status) {
      case 'connected':
        return <Badge variant="default" className="bg-green-500 text-white">Connected</Badge>;
      case 'disconnected':
        return <Badge variant="secondary">Disconnected</Badge>;
      case 'error':
        return <Badge variant="destructive">Error</Badge>;
      case 'pending':
        return <Badge variant="outline">Pending</Badge>;
      default:
        return null;
    }
  };

  const getStatusIcon = () => {
    switch (integration.status) {
      case 'connected':
        return <CheckCircle className="h-5 w-5 text-green-500" />;
      case 'disconnected':
        return <XCircle className="h-5 w-5 text-gray-400" />;
      case 'error':
        return <AlertCircle className="h-5 w-5 text-red-500" />;
      case 'pending':
        return <RefreshCw className="h-5 w-5 text-yellow-500 animate-spin" />;
      default:
        return null;
    }
  };

  return (
    <Card className="hover:shadow-lg transition-shadow">
      <CardContent className="p-8">
        <div className="flex flex-col md:flex-row gap-6">
          <div className="flex-shrink-0">
            <div className={`w-24 h-24 bg-gradient-to-br ${config.color} rounded-lg flex items-center justify-center`}>
              {config.icon}
            </div>
          </div>
          <div className="flex-grow">
            <div className="flex items-center justify-between mb-2">
              <h3 className="text-2xl font-bold">{config.name}</h3>
              {getStatusBadge()}
            </div>
            <p className="text-muted-foreground mb-4">
              {integration.type === 'airbnb' && 'Sync your properties with Airbnb\'s 5+ million listings worldwide.'}
              {integration.type === 'booking' && 'Reach 28+ million listings across 154,000+ destinations.'}
              {integration.type === 'vrbo' && 'Connect with 2+ million vacation rental properties.'}
            </p>
            
            <ul className="space-y-2 mb-6">
              {config.features.map((feature, index) => (
                <li key={index} className="flex items-start gap-2">
                  {getStatusIcon()}
                  <span className="text-sm">{feature}</span>
                </li>
              ))}
            </ul>

            {integration.status === 'error' && (
              <Alert variant="destructive" className="mb-4">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>
                  Connection error. Please check your credentials and try again.
                </AlertDescription>
              </Alert>
            )}

            <div className="flex gap-2 flex-wrap">
              {integration.status === 'connected' ? (
                <>
                  <Button
                    onClick={handleSync}
                    disabled={localSyncing || syncing}
                    variant="outline"
                    size="sm"
                  >
                    <RefreshCw className={`h-4 w-4 mr-2 ${localSyncing ? 'animate-spin' : ''}`} />
                    {localSyncing ? 'Syncing...' : 'Sync Now'}
                  </Button>
                  <Button
                    onClick={() => setShowSettings(!showSettings)}
                    variant="outline"
                    size="sm"
                  >
                    <Settings className="h-4 w-4 mr-2" />
                    Settings
                  </Button>
                  <Button
                    onClick={() => onDisconnect(integration.id)}
                    variant="destructive"
                    size="sm"
                  >
                    Disconnect
                  </Button>
                </>
              ) : (
                <Button
                  onClick={() => onConnect(integration.type)}
                  size="sm"
                >
                  <ExternalLink className="h-4 w-4 mr-2" />
                  Connect {config.name}
                </Button>
              )}
            </div>

            {showSettings && integration.status === 'connected' && (
              <div className="mt-4 p-4 bg-muted rounded-lg">
                <h4 className="font-semibold mb-2">Settings</h4>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Last sync:</span>
                    <span>{integration.last_sync_at ? new Date(integration.last_sync_at).toLocaleString() : 'Never'}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Connected:</span>
                    <span>{new Date(integration.created_at).toLocaleDateString()}</span>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}