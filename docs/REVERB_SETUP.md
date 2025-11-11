# Laravel Reverb Setup (Self-Hosted Real-Time)

Reverb is now installed and configured as the default broadcaster replacing external Pusher.

## 1. Environment Variables

Add (or confirm) in `backend/.env`:
```env
BROADCAST_CONNECTION=reverb
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_APP_ID=renthub-local
REVERB_APP_KEY=renthub-key
REVERB_APP_SECRET=renthub-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SCALING_ENABLED=false
```

Frontend (`frontend/.env.local`):
```env
NEXT_PUBLIC_USE_REVERB=true
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=ws
```

Optional (when disabling Reverb for deployments using Pusher fallback):
```env
NEXT_PUBLIC_USE_REVERB=false
```

## 2. Starting Servers (Local Dev)
```bash
# Backend API
php artisan serve
# WebSocket server
php artisan reverb:start
```

## 3. Echo Client Behavior
In `echo.ts` we check `NEXT_PUBLIC_USE_REVERB`.
- If `true`: connects to `ws://localhost:8080` with pusher protocol shim.
- If `false`: expects Pusher SaaS credentials (`NEXT_PUBLIC_PUSHER_KEY`, etc.).

## 4. Broadcasting Events
Existing events (`UserNotification`, `BookingStatusUpdated`, `NewMessage`) continue to work without changes.

Trigger example:
```php
$user = App\Models\User::first();
event(new App\Events\UserNotification($user, 'Test', 'Reverb works!'));
```

## 5. Private & Presence Channels
Authorization still uses `/broadcasting/auth` provided by `Broadcast::routes()` with Sanctum tokens. No changes required.

## 6. Scaling (Future)
Set `REVERB_SCALING_ENABLED=true` and configure Redis vars if you run multiple Reverb instances behind a load balancer.

## 7. Production Notes
- Run `php artisan reverb:start` under a process supervisor (systemd, Supervisor, or Docker).
- Terminate TLS at reverse proxy (Caddy/Nginx) and forward to internal port 8080.
- Adjust `REVERB_SCHEME=https` + proxy pass wss when enabling SSL.

## 8. Health Check
Add a simple WebSocket connect test in CI before running E2E tests if needed.

## 9. Migration Fallback
You can revert to Pusher by setting:
```env
BROADCAST_CONNECTION=pusher
NEXT_PUBLIC_USE_REVERB=false
```
No code changes required.

## 10. Troubleshooting
| Symptom | Cause | Fix |
|---------|-------|-----|
| Frontend cannot connect | Server not started | Run `php artisan reverb:start` |
| 401 on private channel | Missing Bearer token | Ensure auth header set in Echo config |
| Stale connection | Reverb crashed | Restart process + check logs |
| Mixed content errors | Using ws on https site | Switch to `REVERB_SCHEME=https` & proxy wss |

Logs: view terminal output of `reverb:start`. Add verbosity with `-vv`.

## 11. Next Steps
Proceed with implementing remaining frontend pages (Wishlists first) using real-time updates.
