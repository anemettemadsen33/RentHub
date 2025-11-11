# Production Monitoring Setup

## Overview

RentHub includes production-ready monitoring and observability features:

- **Queue Monitoring** - Real-time queue depth, failed jobs, and health status
- **Prometheus Metrics** - Industry-standard metrics export for Grafana/Prometheus
- **Application Metrics** - Custom counters, histograms, and percentiles
- **Health Checks** - Liveness, readiness, and comprehensive system status

## Endpoints

### Health Checks
```
GET /api/health              # Comprehensive health check
GET /api/health/liveness     # Kubernetes liveness probe
GET /api/health/readiness    # Kubernetes readiness probe
```

### Metrics
```
GET /api/metrics             # JSON format application metrics
GET /api/metrics/prometheus  # Prometheus text format
```

### Queue Monitoring (Admin only)
```
GET  /api/admin/queues                   # Queue statistics and health
POST /api/admin/queues/failed/{id}/retry # Retry a failed job
DELETE /api/admin/queues/failed          # Clear all failed jobs
```

## Prometheus Integration

### Scrape Configuration

Add to your `prometheus.yml`:

```yaml
scrape_configs:
  - job_name: 'renthub'
    scrape_interval: 15s
    static_configs:
      - targets: ['your-app-domain.com']
    metrics_path: '/api/metrics/prometheus'
```

### Available Metrics

**HTTP Metrics:**
- `http_requests_total` - Total HTTP requests (counter)
- `http_request_duration_seconds` - Request latency histogram with p50, p95, p99

**Cache Metrics:**
- `cache_hits_total` - Total cache hits (counter)
- `cache_misses_total` - Total cache misses (counter)
- Calculated hit rate: `100 * (cache_hits / (cache_hits + cache_misses))`

**Queue Metrics:**
- `queue_depth{queue="name"}` - Current jobs in queue (gauge)
- `queue_jobs_processed_total` - Total processed jobs (counter)
- `queue_jobs_failed_total` - Total failed jobs (counter)

## Grafana Dashboard

Import the provided dashboard:

```bash
# Import from file
cat docs/monitoring/grafana-dashboard.json | \
  curl -X POST http://grafana:3000/api/dashboards/db \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer YOUR_API_KEY" \
    -d @-
```

Or manually import `docs/monitoring/grafana-dashboard.json` via Grafana UI.

### Dashboard Panels

1. **HTTP Requests Rate** - Requests per second by route/method
2. **HTTP Request Duration** - p50 and p95 latencies
3. **Cache Hit Rate** - Percentage of cache hits
4. **Queue Depth** - Jobs waiting in each queue
5. **Queue Processing Rate** - Jobs processed vs failed per second
6. **Failed Jobs** - Total failed jobs with color thresholds

## Alerting Rules

### Prometheus Alert Rules

Create `renthub-alerts.yml`:

```yaml
groups:
  - name: renthub
    interval: 30s
    rules:
      # High queue depth
      - alert: HighQueueDepth
        expr: queue_depth > 500
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High queue depth detected"
          description: "Queue {{ $labels.queue }} has {{ $value }} pending jobs"

      # High failure rate
      - alert: HighJobFailureRate
        expr: rate(queue_jobs_failed_total[5m]) > 0.1
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High job failure rate"
          description: "{{ $value }} jobs failing per second"

      # Low cache hit rate
      - alert: LowCacheHitRate
        expr: 100 * (cache_hits_total / (cache_hits_total + cache_misses_total)) < 50
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: "Low cache hit rate"
          description: "Cache hit rate is {{ $value }}%"

      # Slow requests
      - alert: SlowRequests
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 2
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "Slow HTTP requests"
          description: "p95 latency is {{ $value }}s for route {{ $labels.route }}"
```

## Queue Worker Health

Monitor queue workers with systemd or Supervisor:

### Supervisor Configuration

```ini
[program:renthub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=notifications,emails,default --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/renthub/worker.log
stopwaitsecs=3600
```

## Production Recommendations

1. **Enable Redis** for metrics storage:
   ```env
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

2. **Set up log aggregation** (ELK, Loki, CloudWatch)

3. **Configure alerting** via Prometheus Alertmanager or PagerDuty

4. **Monitor queue workers** - Ensure workers are running and healthy

5. **Set TTLs** for old metrics:
   ```bash
   # Clear old histogram data daily
   php artisan schedule:run
   ```

6. **Backup failed jobs** before clearing:
   ```bash
   php artisan queue:failed --format=json > failed-jobs-backup.json
   ```

## Troubleshooting

### Queue Health Issues

```bash
# View queue stats
curl http://your-app/api/admin/queues \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Retry all failed jobs
php artisan queue:retry all

# Clear specific queue
php artisan queue:clear notifications
```

### Metrics Not Updating

- Check Redis connection
- Verify ApiMetricsMiddleware is registered
- Ensure queue workers are processing TrackQueueMetrics jobs

### High Memory Usage

- Review histogram retention in MetricsService
- Implement metric rotation/cleanup
- Consider using external metrics aggregator

## Next Steps

1. Set up Prometheus scraping
2. Import Grafana dashboard
3. Configure alerting rules
4. Enable queue worker monitoring
5. Set up log aggregation
6. Configure uptime monitoring (Pingdom, UptimeRobot)
