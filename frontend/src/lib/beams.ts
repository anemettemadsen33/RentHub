import { Client as BeamsClient } from '@pusher/push-notifications-web';

let beamsClient: BeamsClient | null = null;

export function initBeams() {
  if (typeof window === 'undefined') return null; // SSR guard
  if (beamsClient) return beamsClient;
  const instanceId = process.env.NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID;
  if (!instanceId) {
    if (process.env.NODE_ENV === 'development') {
      console.warn('Pusher Beams instance ID missing (NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID).');
    }
    return null;
  }
  beamsClient = new BeamsClient({ instanceId });
  return beamsClient;
}

export async function startBeams(interests: string[] = []) {
  const client = initBeams();
  if (!client) return null;
  const swReg = typeof window !== 'undefined' && 'serviceWorker' in navigator
    ? await navigator.serviceWorker.register('/pusher-beams-sw.js')
    : undefined;
  if (swReg) {
    const instanceId = process.env.NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID!;
    beamsClient = new BeamsClient({ instanceId, serviceWorkerRegistration: swReg });
  }
  await (beamsClient ?? client).start();
  for (const interest of interests) {
    await (beamsClient ?? client).addDeviceInterest(interest);
  }
  return beamsClient ?? client;
}

export async function addInterest(interest: string) {
  const client = initBeams();
  if (!client) return;
  await client.addDeviceInterest(interest);
}

export async function removeInterest(interest: string) {
  const client = initBeams();
  if (!client) return;
  await client.removeDeviceInterest(interest);
}
