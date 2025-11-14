#!/bin/bash

# Performance Test Script for Conversation APIs
# Compares original vs optimized conversation endpoints

echo "=== Conversation API Performance Test ==="
echo "Timestamp: $(date)"
echo

# Test endpoints
BASE_URL="http://localhost:8000/api/v1"
ORIGINAL_ENDPOINT="$BASE_URL/conversations"
OPTIMIZED_ENDPOINT="$BASE_URL/conversations/optimized"

# Function to test endpoint performance
test_endpoint() {
    local endpoint=$1
    local name=$2
    local iterations=5
    
    echo "Testing $name endpoint: $endpoint"
    echo "Running $iterations iterations..."
    
    total_time=0
    min_time=999999
    max_time=0
    
    for i in $(seq 1 $iterations); do
        echo -n "  Iteration $i: "
        
        # Measure response time
        start_time=$(date +%s%N)
        response=$(curl -s -w "\n%{time_total}" -H "Accept: application/json" "$endpoint" 2>/dev/null)
        end_time=$(date +%s%N)
        
        # Extract time from curl output
        response_time=$(echo "$response" | tail -n1)
        
        # Convert to milliseconds
        response_time_ms=$(echo "$response_time * 1000" | bc -l)
        response_time_ms=$(printf "%.2f" "$response_time_ms")
        
        echo "${response_time_ms}ms"
        
        # Update statistics
        total_time=$(echo "$total_time + $response_time_ms" | bc -l)
        
        if (( $(echo "$response_time_ms < $min_time" | bc -l) )); then
            min_time=$response_time_ms
        fi
        
        if (( $(echo "$response_time_ms > $max_time" | bc -l) )); then
            max_time=$response_time_ms
        fi
        
        # Small delay between requests
        sleep 0.1
    done
    
    # Calculate average
    avg_time=$(echo "scale=2; $total_time / $iterations" | bc -l)
    
    echo
    echo "  Statistics for $name:"
    echo "    Average: ${avg_time}ms"
    echo "    Minimum: ${min_time}ms"
    echo "    Maximum: ${max_time}ms"
    echo "    Total: ${total_time}ms"
    echo
    
    # Return statistics
    echo "$name,$avg_time,$min_time,$max_time,$total_time"
}

# Test health endpoint for baseline
echo "Testing baseline health endpoint..."
health_time=$(curl -s -w "\n%{time_total}" -H "Accept: application/json" "http://localhost:8000/api/health" 2>/dev/null | tail -n1)
health_time_ms=$(echo "$health_time * 1000" | bc -l)
echo "Health check: $(printf "%.2f" "$health_time_ms")ms"
echo

# Test original endpoint
echo "=== Testing Original Conversation API ==="
original_stats=$(test_endpoint "$ORIGINAL_ENDPOINT" "Original")

# Test optimized endpoint  
echo "=== Testing Optimized Conversation API ==="
optimized_stats=$(test_endpoint "$OPTIMIZED_ENDPOINT" "Optimized")

# Generate comparison report
echo "=== Performance Comparison Report ==="
echo "Generated on: $(date)"
echo

echo "Original API Stats:"
echo "$original_stats"
echo

echo "Optimized API Stats:"
echo "$optimized_stats"
echo

# Calculate improvement
original_avg=$(echo "$original_stats" | cut -d',' -f2)
optimized_avg=$(echo "$optimized_stats" | cut -d',' -f2)

if (( $(echo "$original_avg > 0" | bc -l) )); then
    improvement=$(echo "scale=2; (($original_avg - $optimized_avg) / $original_avg) * 100" | bc -l)
    echo "Performance Improvement: ${improvement}%"
    
    if (( $(echo "$improvement > 0" | bc -l) )); then
        echo "✅ Optimized API is faster by ${improvement}%"
    else
        echo "❌ Original API is faster by $(echo "$improvement * -1" | bc -l)%"
    fi
else
    echo "Cannot calculate improvement (original time is 0)"
fi

echo
echo "=== Test Completed ==="