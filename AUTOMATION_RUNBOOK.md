# 🔧 RentHub QA Automation Runbook

**Version:** 1.0.0  
**Last Updated:** 2025-11-13

This runbook contains step-by-step procedures for handling common scenarios with the RentHub QA Automation system.

## 📚 Table of Contents

1. [Common Scenarios](#common-scenarios)
2. [Incident Response](#incident-response)
3. [Maintenance Procedures](#maintenance-procedures)
4. [Troubleshooting](#troubleshooting)
5. [Escalation Procedures](#escalation-procedures)

---

## Common Scenarios

### Scenario 1: E2E Tests Failing

**Symptoms:**
- E2E test workflow fails
- Automated issue created
- QA_STATUS.md shows failures

**Diagnosis Steps:**

1. **Review the workflow run:**
   ```
   Actions → QA Automation Agent → [Failed Run]
   ```

2. **Download and review artifacts:**
   - Download `e2e-test-results` artifact
   - Open `playwright-report/index.html` in browser
   - Identify failing tests

3. **Check for common causes:**
   - Recent code changes that broke functionality
   - Flaky tests (timing issues)
   - Environment configuration issues
   - Test data problems

**Resolution Steps:**

1. **If code change broke functionality:**
   ```bash
   # Fix the actual bug in the code
   # Add/update tests to cover the scenario
   git commit -m "fix: resolve issue causing E2E test failure"
   git push
   ```

2. **If test is flaky:**
   ```bash
   # Update test with better waits/assertions
   cd frontend/tests/e2e
   # Edit the flaky test file
   # Add explicit waits, improve selectors
   git commit -m "test: fix flaky E2E test"
   git push
   ```

3. **If environment issue:**
   ```bash
   # Check workflow configuration
   # Update environment variables if needed
   # Re-run workflow
   ```

**Verification:**
- Re-run the workflow manually
- Confirm all tests pass
- Close the automated issue

---

### Scenario 2: Production Health Check Failing

**Symptoms:**
- Hourly health check workflow fails
- Production alert issue created
- Services may be down or degraded

**Diagnosis Steps:**

1. **Check the health check results:**
   ```
   Actions → Scheduled Health Monitor → [Failed Run]
   ```

2. **Verify service status manually:**
   ```bash
   # Check frontend
   curl -I https://rent-hub-beta.vercel.app
   
   # Check backend
   curl -I https://renthub-tbj7yxj7.on-forge.com/api/health
   ```

3. **Review health logs:**
   ```bash
   cat .github/health-logs/latest.json
   tail -10 .github/health-logs/history.jsonl
   ```

**Resolution Steps:**

1. **If frontend is down:**
   ```bash
   # Check Vercel deployment status
   # Review Vercel logs
   # Redeploy if needed
   ```

2. **If backend is down:**
   ```bash
   # SSH to Forge server
   ssh forge@renthub-tbj7yxj7.on-forge.com
   
   # Check server status
   sudo systemctl status nginx
   sudo systemctl status php8.3-fpm
   
   # Check application logs
   cd ~/renthub-tbj7yxj7.on-forge.com
   tail -100 storage/logs/laravel.log
   
   # Restart services if needed
   sudo systemctl restart php8.3-fpm
   sudo systemctl restart nginx
   ```

3. **If API endpoints failing:**
   ```bash
   # Check database connectivity
   # Review recent deployments
   # Check for configuration issues
   # Review error logs
   ```

**Verification:**
- Wait for next hourly health check
- Manually trigger health check workflow
- Verify all checks pass
- Close or update the alert issue

---

### Scenario 3: Security Vulnerability Detected

**Symptoms:**
- Security scan workflow reports vulnerabilities
- Automated security update PR created
- High-priority issue created

**Diagnosis Steps:**

1. **Review security scan results:**
   ```
   Actions → QA Automation Agent → Security Scanning job
   ```

2. **Check Trivy results:**
   ```
   Security → Code scanning alerts → Trivy
   ```

3. **Check dependency audit:**
   ```bash
   # Frontend
   cd frontend
   npm audit
   
   # Backend
   cd backend
   composer audit
   ```

**Resolution Steps:**

1. **For automated security PR:**
   ```bash
   # Review the PR
   # Check for breaking changes
   # Run tests locally
   # Approve and merge if safe
   ```

2. **For manual fixes required:**
   ```bash
   # Frontend
   cd frontend
   npm audit fix
   # Review changes
   npm test
   git commit -m "fix(security): update vulnerable dependencies"
   
   # Backend
   cd backend
   composer update --with-dependencies [package-name]
   php artisan test
   git commit -m "fix(security): update vulnerable package"
   
   git push
   ```

3. **For unfixable vulnerabilities:**
   ```bash
   # Document the issue
   # Create tracking issue
   # Plan workaround or alternative
   # Update security exceptions if acceptable risk
   ```

**Verification:**
- Re-run security scan
- Verify all critical/high vulnerabilities resolved
- Update security documentation
- Close security issue

---

### Scenario 4: Performance Regression Detected

**Symptoms:**
- Performance audit shows increased build size
- Response times exceed thresholds
- Workflow warnings about performance

**Diagnosis Steps:**

1. **Review performance metrics:**
   ```
   Actions → QA Automation Agent → Performance Audit job
   ```

2. **Check build size analysis:**
   ```bash
   cd frontend
   npm run build
   du -sh .next
   find .next -name "*.js" -exec du -h {} + | sort -rh | head -10
   ```

3. **Identify recent changes:**
   ```bash
   git log --oneline -10
   git diff HEAD~5 -- package.json
   ```

**Resolution Steps:**

1. **If large dependency added:**
   ```bash
   # Consider lighter alternatives
   # Use dynamic imports for large libraries
   # Update to use tree-shaking
   ```

2. **If bundle size increased:**
   ```bash
   # Analyze bundle composition
   # Split large components
   # Optimize imports
   # Remove unused code
   ```

3. **If response time slow:**
   ```bash
   # Review recent API changes
   # Check database query performance
   # Add caching where appropriate
   # Optimize resource-heavy operations
   ```

**Verification:**
- Re-run performance audit
- Compare metrics to baseline
- Document changes and improvements

---

### Scenario 5: Missing Test Coverage Detected

**Symptoms:**
- Automated issue created for new routes
- Coverage analysis shows gaps
- New features without tests

**Diagnosis Steps:**

1. **Review the coverage issue:**
   ```
   Issues → [QA] New Tests Needed
   ```

2. **Check what's missing:**
   ```bash
   # Review the generated recommendations
   cat .github/test-recommendations/needed-tests.md
   ```

3. **Identify new code:**
   ```bash
   git diff --name-only HEAD~5 HEAD
   ```

**Resolution Steps:**

1. **For new frontend pages:**
   ```bash
   cd frontend/tests/e2e
   # Create new test file
   touch new-feature.spec.ts
   ```
   
   Example test:
   ```typescript
   import { test, expect } from '@playwright/test';
   
   test.describe('New Feature', () => {
     test('should load page', async ({ page }) => {
       await page.goto('/new-feature');
       await expect(page).toHaveTitle(/New Feature/);
     });
     
     test('should submit form', async ({ page }) => {
       await page.goto('/new-feature');
       await page.fill('input[name="field"]', 'value');
       await page.click('button[type="submit"]');
       await expect(page.locator('.success')).toBeVisible();
     });
   });
   ```

2. **For new API endpoints:**
   ```bash
   cd backend/tests/Feature/Api
   # Create new test file
   touch NewFeatureTest.php
   ```
   
   Example test:
   ```php
   <?php
   
   namespace Tests\Feature\Api;
   
   use Tests\TestCase;
   
   class NewFeatureTest extends TestCase
   {
       public function test_can_get_resource()
       {
           $response = $this->getJson('/api/v1/new-resource');
           $response->assertOk();
       }
       
       public function test_can_create_resource()
       {
           $response = $this->postJson('/api/v1/new-resource', [
               'field' => 'value',
           ]);
           $response->assertCreated();
       }
   }
   ```

3. **Commit and push:**
   ```bash
   git add .
   git commit -m "test: add coverage for new feature"
   git push
   ```

**Verification:**
- Run test suite
- Verify coverage increased
- Update/close the coverage issue

---

## Incident Response

### Critical Production Outage

**Priority:** P0 - Critical  
**Response Time:** Immediate

1. **Immediate Actions:**
   - Acknowledge the alert
   - Check current status of all services
   - Notify team via appropriate channels

2. **Triage:**
   - Determine scope (frontend, backend, both)
   - Identify affected users
   - Assess severity

3. **Investigation:**
   - Review recent deployments
   - Check server logs
   - Review monitoring dashboards
   - Identify root cause

4. **Resolution:**
   - Apply immediate fix or rollback
   - Verify services restored
   - Monitor for recurrence

5. **Post-Incident:**
   - Document incident timeline
   - Conduct post-mortem
   - Create follow-up issues
   - Update runbooks

---

### Multiple Test Failures

**Priority:** P1 - High  
**Response Time:** 1 hour

1. **Assessment:**
   - Identify common patterns
   - Check if related to single change
   - Determine blast radius

2. **Action:**
   - If single change: consider rollback
   - If widespread: emergency fix session
   - Prioritize critical path tests

3. **Communication:**
   - Update team on status
   - Block deployments until resolved
   - Track progress in incident issue

---

## Maintenance Procedures

### Weekly Maintenance

**Schedule:** Every Monday

1. **Review dependency PRs:**
   - Check automated dependency update PRs
   - Run tests locally
   - Merge if safe

2. **Review automation issues:**
   - Go through all `automated-qa` labeled issues
   - Close resolved issues
   - Triage remaining issues

3. **Check health logs:**
   - Review `.github/health-logs/history.jsonl`
   - Identify trends or patterns
   - Address recurring issues

4. **Update documentation:**
   - Review and update runbooks
   - Document new procedures
   - Update FAQs

---

### Monthly Maintenance

**Schedule:** First day of month

1. **Review metrics:**
   - Test success rates
   - Mean time to detection
   - Mean time to resolution
   - Coverage trends

2. **Optimize workflows:**
   - Review execution times
   - Identify slow tests
   - Optimize where possible

3. **Security review:**
   - Review all security alerts
   - Update dependencies
   - Check SSL certificates
   - Review access logs

4. **Backup verification:**
   - Verify backup processes
   - Test restore procedures
   - Update backup documentation

---

## Troubleshooting

### Workflow Won't Start

**Possible Causes:**
- Workflow file syntax error
- Missing required secrets
- Branch protection rules

**Solutions:**
```bash
# Validate workflow syntax
cat .github/workflows/qa-automation.yml | yaml-lint

# Check secrets are set
# Settings → Secrets and variables → Actions

# Check branch protection
# Settings → Branches → master
```

---

### Tests Pass Locally But Fail in CI

**Possible Causes:**
- Environment differences
- Timing/race conditions
- Missing dependencies
- Hardcoded values

**Solutions:**
```bash
# Replicate CI environment locally
docker run -it node:20 bash

# Run with CI environment variable
CI=true npm run test

# Check for timing issues
# Add explicit waits in tests

# Verify all dependencies in package.json
```

---

### Health Logs Not Updating

**Possible Causes:**
- Git push permission issues
- Workflow disabled
- File path issues

**Solutions:**
```bash
# Check workflow status
# Actions → Scheduled Health Monitor

# Verify GITHUB_TOKEN permissions
# Settings → Actions → General → Workflow permissions

# Manually trigger workflow
# Actions → Scheduled Health Monitor → Run workflow
```

---

## Escalation Procedures

### Level 1: Automated Response
- Automated workflows handle routine checks
- Issues auto-created for failures
- Self-healing for common problems

### Level 2: Developer Response
- Review automated issues
- Fix issues within SLA
- Follow runbook procedures

### Level 3: Team Lead
- Multiple critical failures
- Complex issues requiring coordination
- Decision on rollback vs fix-forward

### Level 4: Engineering Manager
- Production outage > 1 hour
- Security incidents
- Major architectural decisions

---

## Contact Information

### On-Call Rotation
Defined in: [ONCALL.md](ONCALL.md) (if exists)

### Emergency Contacts
- Production Issues: Create issue with `production-alert` label
- Security Issues: Create issue with `security` label
- Questions: Create issue with `help-wanted` label

---

## Appendix

### Useful Commands

```bash
# Check all workflow statuses
gh workflow list

# View latest workflow run
gh run list --limit 1

# View specific workflow
gh run view [run-id]

# Trigger workflow manually
gh workflow run qa-automation.yml

# Check repository secrets
gh secret list

# View health status
cat QA_STATUS.md

# Run manual tests
./scripts/qa-manual-runner.sh
```

### Links
- [Automation Guide](AUTOMATION_QA_GUIDE.md)
- [CI/CD Guide](CI_CD_COMPLETE_GUIDE.md)
- [Testing Guide](TESTING-QUICKSTART.md)
- [GitHub Actions Docs](https://docs.github.com/actions)

---

**Document Owner:** QA Automation Team  
**Last Review:** 2025-11-13  
**Next Review:** 2025-12-13
