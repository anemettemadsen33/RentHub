#!/bin/bash

# Canary Analysis Script
# Analyzes metrics and determines if canary should be promoted

NAMESPACE="production"
PROMETHEUS_URL="http://prometheus.monitoring.svc.cluster.local:9090"
GRAFANA_URL="http://grafana.monitoring.svc.cluster.local:3000"

echo "Analyzing canary deployment metrics..."

# Thresholds
MAX_ERROR_RATE=0.01  # 1%
MAX_P95_LATENCY=2.0  # 2 seconds
MAX_P99_LATENCY=5.0  # 5 seconds
MIN_SUCCESS_RATE=0.99  # 99%

FAILED=0

# Function to query Prometheus
query_prometheus() {
    local query=$1
    curl -s "${PROMETHEUS_URL}/api/v1/query?query=${query}" | jq -r '.data.result[0].value[1]'
}

# 1. Error Rate Analysis
echo "Checking error rate..."
CANARY_ERROR_RATE=$(query_prometheus 'rate(http_requests_total{job="renthub-canary",status=~"5.."}[10m])')
STABLE_ERROR_RATE=$(query_prometheus 'rate(http_requests_total{job="renthub-stable",status=~"5.."}[10m])')

echo "  Canary error rate: ${CANARY_ERROR_RATE}"
echo "  Stable error rate: ${STABLE_ERROR_RATE}"

if (( $(echo "$CANARY_ERROR_RATE > $MAX_ERROR_RATE" | bc -l) )); then
    echo "  ✗ FAILED: Canary error rate too high"
    FAILED=$((FAILED + 1))
else
    echo "  ✓ PASSED"
fi

# 2. Latency Analysis
echo ""
echo "Checking latency..."
CANARY_P95=$(query_prometheus 'histogram_quantile(0.95,rate(http_request_duration_seconds_bucket{job="renthub-canary"}[10m]))')
CANARY_P99=$(query_prometheus 'histogram_quantile(0.99,rate(http_request_duration_seconds_bucket{job="renthub-canary"}[10m]))')
STABLE_P95=$(query_prometheus 'histogram_quantile(0.95,rate(http_request_duration_seconds_bucket{job="renthub-stable"}[10m]))')

echo "  Canary P95: ${CANARY_P95}s"
echo "  Canary P99: ${CANARY_P99}s"
echo "  Stable P95: ${STABLE_P95}s"

if (( $(echo "$CANARY_P95 > $MAX_P95_LATENCY" | bc -l) )); then
    echo "  ✗ FAILED: Canary P95 latency too high"
    FAILED=$((FAILED + 1))
elif (( $(echo "$CANARY_P99 > $MAX_P99_LATENCY" | bc -l) )); then
    echo "  ✗ FAILED: Canary P99 latency too high"
    FAILED=$((FAILED + 1))
else
    echo "  ✓ PASSED"
fi

# 3. Success Rate
echo ""
echo "Checking success rate..."
CANARY_SUCCESS=$(query_prometheus 'rate(http_requests_total{job="renthub-canary",status=~"2.."}[10m])')
CANARY_TOTAL=$(query_prometheus 'rate(http_requests_total{job="renthub-canary"}[10m])')
CANARY_SUCCESS_RATE=$(echo "scale=4; $CANARY_SUCCESS / $CANARY_TOTAL" | bc)

echo "  Canary success rate: ${CANARY_SUCCESS_RATE}"

if (( $(echo "$CANARY_SUCCESS_RATE < $MIN_SUCCESS_RATE" | bc -l) )); then
    echo "  ✗ FAILED: Canary success rate too low"
    FAILED=$((FAILED + 1))
else
    echo "  ✓ PASSED"
fi

# 4. Resource Usage
echo ""
echo "Checking resource usage..."
CANARY_CPU=$(kubectl top pods -n $NAMESPACE -l app=renthub,version=canary --no-headers | awk '{sum+=$2} END {print sum}')
CANARY_MEMORY=$(kubectl top pods -n $NAMESPACE -l app=renthub,version=canary --no-headers | awk '{sum+=$3} END {print sum}')

echo "  Canary CPU: ${CANARY_CPU}"
echo "  Canary Memory: ${CANARY_MEMORY}"

# 5. Database Performance
echo ""
echo "Checking database performance..."
DB_QUERY_TIME=$(query_prometheus 'rate(mysql_global_status_queries[5m])')
echo "  Database query rate: ${DB_QUERY_TIME}"

# 6. Cache Hit Rate
echo ""
echo "Checking cache performance..."
CACHE_HIT_RATE=$(query_prometheus 'rate(redis_keyspace_hits_total[5m])/(rate(redis_keyspace_hits_total[5m])+rate(redis_keyspace_misses_total[5m]))')
echo "  Cache hit rate: ${CACHE_HIT_RATE}"

# 7. Business Metrics
echo ""
echo "Checking business metrics..."
CANARY_BOOKINGS=$(query_prometheus 'increase(bookings_created_total{version="canary"}[10m])')
STABLE_BOOKINGS=$(query_prometheus 'increase(bookings_created_total{version="stable"}[10m])')
echo "  Canary bookings: ${CANARY_BOOKINGS}"
echo "  Stable bookings: ${STABLE_BOOKINGS}"

# Summary
echo ""
echo "========================================="
if [ $FAILED -eq 0 ]; then
    echo "✓ Canary analysis PASSED - Safe to promote"
    exit 0
else
    echo "✗ Canary analysis FAILED - ${FAILED} check(s) failed"
    echo "Rolling back canary deployment"
    exit 1
fi
