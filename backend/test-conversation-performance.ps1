# Performance Test Script for Conversation APIs (PowerShell)
# Compares original vs optimized conversation endpoints

Write-Host "=== Conversation API Performance Test ===" -ForegroundColor Green
Write-Host "Timestamp: $(Get-Date)"
Write-Host

# Test endpoints
$BASE_URL = "http://localhost:8000/api/v1"
$ORIGINAL_ENDPOINT = "$BASE_URL/conversations"
$OPTIMIZED_ENDPOINT = "$BASE_URL/conversations/optimized"

# Function to test endpoint performance
function Test-Endpoint {
    param(
        [string]$endpoint,
        [string]$name,
        [int]$iterations = 5
    )
    
    Write-Host "Testing $name endpoint: $endpoint" -ForegroundColor Yellow
    Write-Host "Running $iterations iterations..."
    
    $times = @()
    $totalTime = 0
    $minTime = [double]::MaxValue
    $maxTime = 0
    
    for ($i = 1; $i -le $iterations; $i++) {
        Write-Host -NoNewline "  Iteration $i: "
        
        try {
            # Measure response time
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
            $response = Invoke-RestMethod -Uri $endpoint -Method GET -Headers @{"Accept" = "application/json"} -TimeoutSec 30
            $stopwatch.Stop()
            
            # Convert to milliseconds
            $responseTimeMs = [math]::Round($stopwatch.Elapsed.TotalMilliseconds, 2)
            
            Write-Host "${responseTimeMs}ms" -ForegroundColor White
            
            # Update statistics
            $times += $responseTimeMs
            $totalTime += $responseTimeMs
            
            if ($responseTimeMs -lt $minTime) { $minTime = $responseTimeMs }
            if ($responseTimeMs -gt $maxTime) { $maxTime = $responseTimeMs }
            
        } catch {
            Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
            $times += 0
        }
        
        # Small delay between requests
        Start-Sleep -Milliseconds 100
    }
    
    # Calculate average
    $avgTime = if ($times.Count -gt 0) { [math]::Round(($times | Measure-Object -Average).Average, 2) } else { 0 }
    
    Write-Host
    Write-Host "  Statistics for $name:" -ForegroundColor Cyan
    Write-Host "    Average: ${avgTime}ms" -ForegroundColor White
    Write-Host "    Minimum: ${minTime}ms" -ForegroundColor White
    Write-Host "    Maximum: ${maxTime}ms" -ForegroundColor White
    Write-Host "    Total: ${totalTime}ms" -ForegroundColor White
    Write-Host
    
    return @{
        Name = $name
        Average = $avgTime
        Minimum = $minTime
        Maximum = $maxTime
        Total = $totalTime
        Times = $times
    }
}

# Test health endpoint for baseline
Write-Host "Testing baseline health endpoint..." -ForegroundColor Yellow
try {
    $healthStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    $healthResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/health" -Method GET -Headers @{"Accept" = "application/json"} -TimeoutSec 30
    $healthStopwatch.Stop()
    $healthTimeMs = [math]::Round($healthStopwatch.Elapsed.TotalMilliseconds, 2)
    Write-Host "Health check: ${healthTimeMs}ms" -ForegroundColor White
} catch {
    Write-Host "Health check ERROR: $($_.Exception.Message)" -ForegroundColor Red
    $healthTimeMs = 0
}
Write-Host

# Test original endpoint
Write-Host "=== Testing Original Conversation API ===" -ForegroundColor Blue
$originalResults = Test-Endpoint -endpoint $ORIGINAL_ENDPOINT -name "Original"

# Test optimized endpoint  
Write-Host "=== Testing Optimized Conversation API ===" -ForegroundColor Blue
$optimizedResults = Test-Endpoint -endpoint $OPTIMIZED_ENDPOINT -name "Optimized"

# Generate comparison report
Write-Host "=== Performance Comparison Report ===" -ForegroundColor Green
Write-Host "Generated on: $(Get-Date)"
Write-Host

Write-Host "Original API Results:" -ForegroundColor Cyan
Write-Host "  Average: $($originalResults.Average)ms" -ForegroundColor White
Write-Host "  Minimum: $($originalResults.Minimum)ms" -ForegroundColor White
Write-Host "  Maximum: $($originalResults.Maximum)ms" -ForegroundColor White
Write-Host

Write-Host "Optimized API Results:" -ForegroundColor Cyan
Write-Host "  Average: $($optimizedResults.Average)ms" -ForegroundColor White
Write-Host "  Minimum: $($optimizedResults.Minimum)ms" -ForegroundColor White
Write-Host "  Maximum: $($optimizedResults.Maximum)ms" -ForegroundColor White
Write-Host

# Calculate improvement
if ($originalResults.Average -gt 0 -and $optimizedResults.Average -gt 0) {
    $improvement = [math]::Round((($originalResults.Average - $optimizedResults.Average) / $originalResults.Average) * 100, 2)
    Write-Host "Performance Improvement: ${improvement}%" -ForegroundColor Yellow
    
    if ($improvement -gt 0) {
        Write-Host "✅ Optimized API is faster by ${improvement}%" -ForegroundColor Green
    } else {
        Write-Host "❌ Original API is faster by $([math]::Abs($improvement))%" -ForegroundColor Red
    }
} else {
    Write-Host "Cannot calculate improvement (one of the APIs returned 0ms)" -ForegroundColor Yellow
}

Write-Host
Write-Host "=== Test Completed ===" -ForegroundColor Green

# Export results to CSV for further analysis
$csvData = @()
$csvData += [PSCustomObject]@{
    Endpoint = "Original"
    Average_ms = $originalResults.Average
    Minimum_ms = $originalResults.Minimum
    Maximum_ms = $originalResults.Maximum
    Total_ms = $originalResults.Total
}

$csvData += [PSCustomObject]@{
    Endpoint = "Optimized"
    Average_ms = $optimizedResults.Average
    Minimum_ms = $optimizedResults.Minimum
    Maximum_ms = $optimizedResults.Maximum
    Total_ms = $optimizedResults.Total
}

$csvData | Export-Csv -Path "conversation-performance-results.csv" -NoTypeInformation
Write-Host "Results exported to conversation-performance-results.csv" -ForegroundColor Green