#!/bin/bash

# Canary Metrics Checker
# Monitors canary deployment metrics and decides if rollout should continue

set -e

NAMESPACE="renthub-prod"
CANARY_DEPLOYMENT="backend-canary"
PROMETHEUS_URL="${PROMETHEUS_URL:-http://prometheus:9090}"
THRESHOLD_ERROR_RATE=5.0
THRESHOLD_LATENCY_P95=2000

echo "🔍 Checking canary metrics..."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if canary pods are healthy
echo -n "Checking canary pod health... "
healthy_pods=$(kubectl get pods -n $NAMESPACE -l app=backend,version=canary -o jsonpath='{.items[?(@.status.phase=="Running")].metadata.name}' | wc -w)
total_pods=$(kubectl get pods -n $NAMESPACE -l app=backend,version=canary -o jsonpath='{.items[*].metadata.name}' | wc -w)

if [ $healthy_pods -eq $total_pods ] && [ $total_pods -gt 0 ]; then
    echo -e "${GREEN}✓ PASS${NC} ($healthy_pods/$total_pods pods healthy)"
else
    echo -e "${RED}✗ FAIL${NC} (Only $healthy_pods/$total_pods pods healthy)"
    exit 1
fi

# Check error rate
echo -n "Checking error rate... "
error_rate=$(curl -s "${PROMETHEUS_URL}/api/v1/query" \
    --data-urlencode "query=sum(rate(http_requests_total{job=\"backend\",version=\"canary\",status=~\"5..\"}[5m])) / sum(rate(http_requests_total{job=\"backend\",version=\"canary\"}[5m])) * 100" \
    | jq -r '.data.result[0].value[1]' 2>/dev/null || echo "0")

if (( $(echo "$error_rate < $THRESHOLD_ERROR_RATE" | bc -l) )); then
    echo -e "${GREEN}✓ PASS${NC} (${error_rate}% < ${THRESHOLD_ERROR_RATE}%)"
else
    echo -e "${RED}✗ FAIL${NC} (${error_rate}% >= ${THRESHOLD_ERROR_RATE}%)"
    exit 1
fi

# Check latency (P95)
echo -n "Checking P95 latency... "
latency=$(curl -s "${PROMETHEUS_URL}/api/v1/query" \
    --data-urlencode "query=histogram_quantile(0.95, rate(http_request_duration_seconds_bucket{job=\"backend\",version=\"canary\"}[5m])) * 1000" \
    | jq -r '.data.result[0].value[1]' 2>/dev/null || echo "0")

if (( $(echo "$latency < $THRESHOLD_LATENCY_P95" | bc -l) )); then
    echo -e "${GREEN}✓ PASS${NC} (${latency}ms < ${THRESHOLD_LATENCY_P95}ms)"
else
    echo -e "${RED}✗ FAIL${NC} (${latency}ms >= ${THRESHOLD_LATENCY_P95}ms)"
    exit 1
fi

# Check memory usage
echo -n "Checking memory usage... "
memory_usage=$(kubectl top pods -n $NAMESPACE -l app=backend,version=canary --no-headers | awk '{print $3}' | sed 's/Mi//' | awk '{sum+=$1} END {print sum/NR}')
memory_limit=4096

if (( $(echo "$memory_usage < $memory_limit * 0.9" | bc -l) )); then
    echo -e "${GREEN}✓ PASS${NC} (${memory_usage}Mi < ${memory_limit}Mi)"
else
    echo -e "${YELLOW}⚠ WARNING${NC} (${memory_usage}Mi approaching limit)"
fi

# Check CPU usage
echo -n "Checking CPU usage... "
cpu_usage=$(kubectl top pods -n $NAMESPACE -l app=backend,version=canary --no-headers | awk '{print $2}' | sed 's/m//' | awk '{sum+=$1} END {print sum/NR}')
cpu_limit=2000

if (( $(echo "$cpu_usage < $cpu_limit * 0.9" | bc -l) )); then
    echo -e "${GREEN}✓ PASS${NC} (${cpu_usage}m < ${cpu_limit}m)"
else
    echo -e "${YELLOW}⚠ WARNING${NC} (${cpu_usage}m approaching limit)"
fi

echo -e "\n${GREEN}✓ Canary metrics look good! Ready to continue rollout.${NC}"
exit 0
