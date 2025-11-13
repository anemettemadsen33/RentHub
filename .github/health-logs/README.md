# Health Logs

This directory contains health monitoring data for the RentHub application.

## Files

- **latest.json** - Most recent health check results
- **history.jsonl** - Complete history of health checks (JSONL format - one JSON object per line)

## Usage

### View Latest Status
```bash
cat .github/health-logs/latest.json | jq
```

### View History
```bash
# Last 10 checks
tail -10 .github/health-logs/history.jsonl | jq

# All checks from last 24 hours
cat .github/health-logs/history.jsonl | \
  jq -s 'map(select(.timestamp > (now - 86400 | strftime("%Y-%m-%dT%H:%M:%SZ"))))'
```

### Analyze Trends
```bash
# Count failures in last 100 checks
tail -100 .github/health-logs/history.jsonl | \
  jq -s 'map(select(.frontend.healthy == false or .backend.healthy == false)) | length'
```

## Automated Updates

This directory is automatically updated by:
- Scheduled Health Monitor workflow (hourly)
- Manual workflow triggers

Do not manually edit these files - they are managed by automation.
