# Infrastructure Scaling Implementation Guide

## Overview
Complete infrastructure scaling implementation for RentHub including horizontal scaling, load balancing, auto-scaling, database replication, monitoring, and logging.

**Status**: ✅ Complete  
**Last Updated**: November 3, 2025  
**Task**: 5.3 Infrastructure Scaling

---

## 🎯 Features Implemented

### 1. Horizontal Scaling ✅

#### Load Balancing
- **Configuration**: `backend/config/scaling.php`
- **Algorithms**: Round Robin, Least Connections, IP Hash
- **Health Checks**: Automatic node health monitoring
- **Sticky Sessions**: Session persistence support

**Configuration**:
```php
'load_balancer' => [
    'enabled' => true,
    'algorithm' => 'round_robin',
    'health_check_interval' => 30,
    'nodes' => [
        ['host' => '10.0.1.10', 'port' => 80, 'weight' => 1],
        ['host' => '10.0.1.11', 'port' => 80, 'weight' => 1],
    ],
],
```

#### Auto-Scaling
- **Providers**: AWS, Azure, GCP
- **Metrics-Based**: CPU, Memory, Requests
- **Cooldown Periods**: Prevents flapping
- **Instance Limits**: Min/Max configuration

**Configuration**:
```php
'auto_scaling' => [
    'enabled' => true,
    'min_instances' => 2,
    'max_instances' => 10,
    'metrics' => [
        'cpu' => ['target' => 70],
        'memory' => ['target' => 75],
        'requests' => ['target' => 1000],
    ],
],
```

#### Database Replication
- **Read Replicas**: Multiple read-only replicas
- **Write Master**: Single master for writes
- **Sticky Sessions**: Query consistency
- **Automatic Failover**: Master promotion

**Configuration**:
```php
'database_replication' => [
    'enabled' => true,
    'read_replicas' => [
        ['host' => 'db-replica-1', 'weight' => 1],
        ['host' => 'db-replica-2', 'weight' => 1],
    ],
    'write_master' => [
        'host' => 'db-master',
    ],
],
```

#### Microservices Architecture
- **Service Gateway**: API gateway
- **Service Discovery**: Automatic registration
- **Circuit Breaker**: Fault tolerance
- **Service-to-Service**: Secure communication

**Configured Services**:
- Auth Service
- Properties Service
- Bookings Service
- Payments Service
- Notifications Service

### 2. Monitoring & Logging ✅

#### Application Monitoring

**Supported Providers**:
1. **New Relic**
   - Transaction tracing
   - Error collector
   - Browser monitoring
   - Custom metrics

2. **DataDog**
   - APM (Application Performance Monitoring)
   - Metrics collection
   - Log aggregation
   - Distributed tracing

3. **Prometheus**
   - Time-series metrics
   - Pull-based model
   - Custom collectors
   - Grafana integration

**Configuration**: `backend/config/monitoring.php`

#### Error Tracking (Sentry)
- **Real-time Errors**: Immediate notification
- **Stack Traces**: Detailed error context
- **Breadcrumbs**: Event trail
- **Release Tracking**: Version-based errors
- **Performance Monitoring**: Transaction traces

**Features**:
```php
'sentry' => [
    'enabled' => true,
    'traces_sample_rate' => 0.1,
    'breadcrumbs' => [
        'sql_queries' => true,
        'queue_info' => true,
    ],
],
```

#### Log Aggregation

**Supported Providers**:
1. **ELK Stack** (Elasticsearch, Logstash, Kibana)
2. **Splunk**
3. **AWS CloudWatch**

**Features**:
- Centralized logging
- Log parsing and indexing
- Search and analysis
- Real-time monitoring
- Log retention policies

#### Uptime Monitoring

**Providers**:
- Pingdom
- UptimeRobot
- StatusPage.io

**Checks**:
- HTTP endpoint monitoring
- SSL certificate expiry
- Response time tracking
- Multi-region checks

---

## 📁 Files Created

### Configuration Files (2)
```
backend/config/
├── scaling.php       # Scaling configuration
└── monitoring.php    # Monitoring configuration
```

### Controllers (1)
```
backend/app/Http/Controllers/Api/
└── HealthCheckController.php  # Health check endpoints
```

### Services (1)
```
backend/app/Services/
└── MonitoringService.php  # Monitoring integration
```

### Routes (1)
```
backend/routes/
└── api.php  # Updated with health check routes
```

---

## 🔌 Health Check Endpoints

### Comprehensive Health Check
```bash
GET /api/health
```

**Response**:
```json
{
  "status": "healthy",
  "timestamp": "2025-11-03T12:00:00Z",
  "environment": "production",
  "version": "1.0.0",
  "checks": {
    "database": {
      "healthy": true,
      "latency_ms": 5.2,
      "connection": "renthub"
    },
    "redis": {
      "healthy": true,
      "latency_ms": 1.1
    },
    "cache": {
      "healthy": true,
      "latency_ms": 2.3,
      "driver": "redis"
    },
    "storage": {
      "healthy": true,
      "disk_usage_percent": 45.6,
      "free_space_gb": 128.5
    },
    "queue": {
      "healthy": true,
      "size": 15,
      "status": "normal"
    }
  },
  "resources": {
    "memory": {
      "current_mb": 128.5,
      "peak_mb": 156.2,
      "limit_mb": "512M"
    },
    "cpu_load": {
      "1min": 0.5,
      "5min": 0.6,
      "15min": 0.4
    },
    "uptime_seconds": 345600
  }
}
```

### Liveness Check
```bash
GET /api/health/liveness
```

**Response**:
```json
{
  "status": "alive",
  "timestamp": "2025-11-03T12:00:00Z"
}
```

### Readiness Check
```bash
GET /api/health/readiness
```

**Response**:
```json
{
  "status": "ready",
  "checks": {
    "database": "ready",
    "redis": "ready"
  },
  "timestamp": "2025-11-03T12:00:00Z"
}
```

### Metrics Endpoint
```bash
GET /api/metrics
```

**Response**:
```json
{
  "timestamp": "2025-11-03T12:00:00Z",
  "application": {
    "name": "RentHub",
    "env": "production",
    "version": "1.0.0"
  },
  "performance": {
    "memory_usage_mb": 128.5,
    "peak_memory_mb": 156.2,
    "uptime_seconds": 345600
  },
  "database": {
    "connection": "active",
    "latency_ms": 5.2
  },
  "cache": {
    "driver": "redis",
    "latency_ms": 1.1,
    "status": "operational"
  },
  "queue": {
    "connection": "redis",
    "size": 15,
    "status": "operational"
  }
}
```

---

## 💻 Monitoring Service Usage

### Track Custom Metrics

```php
use App\Services\MonitoringService;

$monitoring = new MonitoringService();

// Track metric
$monitoring->metric('bookings.created', 1, [
    'property_type' => 'apartment',
    'city' => 'New York',
]);

// Track event
$monitoring->event('payment_processed', [
    'amount' => 2500,
    'currency' => 'USD',
    'method' => 'stripe',
]);
```

### Transaction Tracing

```php
// Start transaction
$monitoring->startTransaction('process_booking');

// ... business logic ...

// End transaction
$monitoring->endTransaction();
```

### Error Tracking

```php
try {
    // Your code
} catch (\Exception $e) {
    $monitoring->recordException($e, [
        'user_id' => auth()->id(),
        'request_id' => request()->id(),
    ]);
}
```

### Alerting

```php
$monitoring->sendAlert(
    'High Error Rate Detected',
    'Error rate has exceeded 5% in the last 5 minutes',
    'critical',
    ['error_count' => 150, 'threshold' => 100]
);
```

---

## 🔧 Environment Configuration

### Load Balancing
```env
LOAD_BALANCER_ENABLED=true
LOAD_BALANCER_ALGORITHM=round_robin
LOAD_BALANCER_HEALTH_CHECK=30

APP_NODE_1_HOST=10.0.1.10
APP_NODE_1_PORT=80
APP_NODE_1_WEIGHT=1

APP_NODE_2_HOST=10.0.1.11
APP_NODE_2_PORT=80
APP_NODE_2_WEIGHT=1
```

### Auto-Scaling
```env
AUTO_SCALING_ENABLED=true
AUTO_SCALING_PROVIDER=aws
AUTO_SCALING_MIN_INSTANCES=2
AUTO_SCALING_MAX_INSTANCES=10
AUTO_SCALING_CPU_TARGET=70
AUTO_SCALING_MEMORY_TARGET=75
```

### Database Replication
```env
DB_REPLICATION_ENABLED=true
DB_READ_REPLICA_1_HOST=db-replica-1.example.com
DB_READ_REPLICA_1_PORT=3306
DB_READ_REPLICA_2_HOST=db-replica-2.example.com
DB_READ_REPLICA_2_PORT=3306
```

### Monitoring
```env
# Monitoring Provider
MONITORING_ENABLED=true
MONITORING_PROVIDER=datadog

# DataDog
DATADOG_ENABLED=true
DATADOG_API_KEY=your-api-key
DATADOG_APP_KEY=your-app-key
DATADOG_SERVICE_NAME=renthub
DATADOG_ENV=production

# Sentry
SENTRY_ENABLED=true
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project
SENTRY_TRACES_SAMPLE_RATE=0.1

# Alerts
ALERTS_ENABLED=true
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
SLACK_ALERT_CHANNEL=#alerts
ALERT_EMAIL_RECIPIENTS=ops@renthub.com,dev@renthub.com
```

### Log Aggregation
```env
LOG_AGGREGATION_ENABLED=true
LOG_AGGREGATION_PROVIDER=elk

# ELK Stack
ELASTICSEARCH_HOSTS=elasticsearch:9200
ELASTICSEARCH_INDEX_PREFIX=renthub
LOGSTASH_HOST=logstash
LOGSTASH_PORT=5044
```

---

## 📊 Monitoring Dashboards

### DataDog Dashboard

**Key Metrics**:
- Request rate and latency
- Error rate and types
- Database query performance
- Cache hit/miss ratio
- Queue depth and processing time
- Memory and CPU usage
- Custom business metrics

**Alerts**:
- High error rate
- Slow response times
- Database connection issues
- Queue backlog
- Memory/CPU thresholds

### New Relic Dashboard

**Apdex Score**: Application performance index  
**Transaction Traces**: Slow transactions  
**Error Analytics**: Error patterns and trends  
**Database Analysis**: Query performance  
**External Services**: API call tracking

### Prometheus + Grafana

**Panels**:
- HTTP request rate
- Response time percentiles (p50, p95, p99)
- Error rate by endpoint
- Database connection pool
- Cache operations
- Queue metrics

---

## 🚨 Alerting Rules

### Critical Alerts

1. **High Error Rate**
   - Threshold: > 5%
   - Window: 5 minutes
   - Channel: PagerDuty, Slack, Email

2. **Database Connection Errors**
   - Threshold: > 3 errors
   - Window: 1 minute
   - Channel: PagerDuty, Email

3. **High Memory Usage**
   - Threshold: > 85%
   - Duration: 10 minutes
   - Channel: PagerDuty, Slack

### Warning Alerts

1. **Slow Response Times**
   - Threshold: > 3000ms
   - Count: 10 requests
   - Channel: Slack, Email

2. **High CPU Usage**
   - Threshold: > 80%
   - Duration: 10 minutes
   - Channel: Slack

3. **Queue Backlog**
   - Threshold: > 1000 jobs
   - Channel: Slack

---

## 🐳 Docker & Kubernetes

### Docker Compose Example

```yaml
version: '3.8'

services:
  app:
    build: .
    environment:
      - MONITORING_ENABLED=true
      - DATADOG_API_KEY=${DATADOG_API_KEY}
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '1'
          memory: 512M
      restart_policy:
        condition: on-failure

  db:
    image: mysql:8.0
    deploy:
      replicas: 1

  redis:
    image: redis:7-alpine
    deploy:
      replicas: 1

  datadog-agent:
    image: gcr.io/datadoghq/agent:latest
    environment:
      - DD_API_KEY=${DATADOG_API_KEY}
      - DD_APM_ENABLED=true
      - DD_LOGS_ENABLED=true
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - /proc/:/host/proc/:ro
      - /sys/fs/cgroup/:/host/sys/fs/cgroup:ro
```

### Kubernetes Deployment

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: renthub-api
spec:
  replicas: 3
  selector:
    matchLabels:
      app: renthub-api
  template:
    metadata:
      labels:
        app: renthub-api
    spec:
      containers:
      - name: app
        image: renthub/api:latest
        ports:
        - containerPort: 80
        env:
        - name: MONITORING_ENABLED
          value: "true"
        - name: DATADOG_API_KEY
          valueFrom:
            secretKeyRef:
              name: datadog
              key: api-key
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /api/health/liveness
            port: 80
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /api/health/readiness
            port: 80
          initialDelaySeconds: 10
          periodSeconds: 5
---
apiVersion: v1
kind: Service
metadata:
  name: renthub-api
spec:
  selector:
    app: renthub-api
  ports:
  - protocol: TCP
    port: 80
    targetPort: 80
  type: LoadBalancer
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: renthub-api-hpa
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: renthub-api
  minReplicas: 2
  maxReplicas: 10
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 75
```

---

## 📈 Performance Optimization

### Database Connection Pooling

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'port' => env('DB_PORT'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => true,
    ],
    'pool' => [
        'min' => 5,
        'max' => 20,
    ],
],
```

### Redis Cluster

```env
REDIS_CLIENT=phpredis
REDIS_CLUSTER=redis

REDIS_CLUSTER_NODE_1=127.0.0.1:6379
REDIS_CLUSTER_NODE_2=127.0.0.1:6380
REDIS_CLUSTER_NODE_3=127.0.0.1:6381
```

### Queue Workers Scaling

```bash
# Supervisor configuration
[program:renthub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=5
redirect_stderr=true
stdout_logfile=/var/log/renthub-worker.log
```

---

## 🔗 Resources

- [AWS Auto Scaling](https://aws.amazon.com/autoscaling/)
- [DataDog APM](https://www.datadoghq.com/product/apm/)
- [New Relic Documentation](https://docs.newrelic.com/)
- [Sentry Documentation](https://docs.sentry.io/)
- [Prometheus Documentation](https://prometheus.io/docs/)
- [ELK Stack Guide](https://www.elastic.co/what-is/elk-stack)
- [Kubernetes Best Practices](https://kubernetes.io/docs/concepts/configuration/overview/)

---

**Status**: ✅ Complete  
**Version**: 1.0.0  
**Last Updated**: November 3, 2025
