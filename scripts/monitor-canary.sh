#!/bin/bash

# Canary Monitoring Script
# Usage: ./monitor-canary.sh <duration_in_minutes>

DURATION=$1
NAMESPACE="production"
CANARY_DEPLOYMENT="renthub-canary"
PROMETHEUS_URL="http://prometheus.monitoring.svc.cluster.local:9090"

echo "Monitoring canary deployment for ${DURATION} minutes..."

END_TIME=$((SECONDS + DURATION * 60))

while [ $SECONDS -lt $END_TIME ]; do
    echo ""
    echo "Time remaining: $(( (END_TIME - SECONDS) / 60 )) minutes"
    
    # Check pod health
    READY_PODS=$(kubectl get deployment $CANARY_DEPLOYMENT -n $NAMESPACE -o jsonpath='{.status.readyReplicas}')
    DESIRED_PODS=$(kubectl get deployment $CANARY_DEPLOYMENT -n $NAMESPACE -o jsonpath='{.spec.replicas}')
    
    echo "Pod status: ${READY_PODS}/${DESIRED_PODS} ready"
    
    if [ "$READY_PODS" != "$DESIRED_PODS" ]; then
        echo "WARNING: Not all pods are ready!"
    fi
    
    # Check error rate from Prometheus
    ERROR_RATE=$(curl -s "${PROMETHEUS_URL}/api/v1/query?query=rate(http_requests_total{job=\"renthub-canary\",status=~\"5..\"}[5m])" | jq -r '.data.result[0].value[1]')
    
    if [ ! -z "$ERROR_RATE" ]; then
        echo "Error rate: ${ERROR_RATE}%"
        
        # Alert if error rate > 1%
        if (( $(echo "$ERROR_RATE > 0.01" | bc -l) )); then
            echo "ERROR: Error rate too high!"
            exit 1
        fi
    fi
    
    # Check response time
    RESPONSE_TIME=$(curl -s "${PROMETHEUS_URL}/api/v1/query?query=histogram_quantile(0.95,rate(http_request_duration_seconds_bucket{job=\"renthub-canary\"}[5m]))" | jq -r '.data.result[0].value[1]')
    
    if [ ! -z "$RESPONSE_TIME" ]; then
        echo "P95 response time: ${RESPONSE_TIME}s"
        
        # Alert if P95 > 2s
        if (( $(echo "$RESPONSE_TIME > 2" | bc -l) )); then
            echo "WARNING: Response time degraded!"
        fi
    fi
    
    # Check CPU usage
    CPU_USAGE=$(kubectl top pods -n $NAMESPACE -l app=renthub,version=canary --no-headers | awk '{sum+=$2} END {print sum}')
    echo "CPU usage: ${CPU_USAGE}"
    
    # Check memory usage
    MEMORY_USAGE=$(kubectl top pods -n $NAMESPACE -l app=renthub,version=canary --no-headers | awk '{sum+=$3} END {print sum}')
    echo "Memory usage: ${MEMORY_USAGE}"
    
    sleep 60
done

echo ""
echo "Monitoring complete!"
