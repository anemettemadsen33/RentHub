# 🤖 RentHub QA Automation - Quick Reference

**Version:** 1.0.0 | **Updated:** 2025-11-13

## 🎯 Quick Commands

```bash
# View current status
cat QA_STATUS.md

# Check health logs
cat .github/health-logs/latest.json

# Run manual tests
./scripts/qa-manual-runner.sh

# View workflow status
gh workflow list

# Trigger QA workflow
gh workflow run qa-automation.yml

# Trigger health check
gh workflow run health-monitor.yml
```

## 📊 Workflow Overview

| Workflow | Trigger | Frequency | Purpose |
|----------|---------|-----------|---------|
| QA Automation Agent | Push/PR/Schedule | Every 6h | E2E, API, Security, Performance |
| Code Quality | PR | On-demand | Linting, Type checking |
| Health Monitor | Schedule | Every hour | Production monitoring |
| Dependency Updates | Schedule | Weekly Mon | Auto-update deps |
| Test Suite Update | Code change | On push | Coverage tracking |

## 🔗 Quick Links

### Documentation
- [📚 Full Guide](AUTOMATION_QA_GUIDE.md)
- [🔧 Runbook](AUTOMATION_RUNBOOK.md)
- [📊 Status](QA_STATUS.md)

### GitHub
- [▶️ Actions](../../actions)
- [🐛 Issues](../../issues?q=label%3Aautomated-qa)
- [🔐 Security](../../security)

## 🚨 Common Issues

### E2E Tests Failing
```bash
# 1. Check workflow logs
# Actions → QA Automation Agent → [Failed Run]

# 2. Download artifacts
# Download e2e-test-results artifact

# 3. Fix and push
git commit -m "fix: resolve E2E test failure"
git push
```

### Production Down Alert
```bash
# 1. Check services
curl -I https://rent-hub-beta.vercel.app
curl -I https://renthub-tbj7yxj7.on-forge.com/api/health

# 2. Review logs
cat .github/health-logs/latest.json

# 3. Check deployment status
# Vercel: https://vercel.com/dashboard
# Forge: SSH and check logs
```

### Security Vulnerability
```bash
# 1. Review automated PR
# Check pull requests for "Security Updates"

# 2. Or fix manually
cd frontend && npm audit fix
cd backend && composer update [package]

# 3. Commit and push
git commit -m "fix(security): update vulnerable deps"
git push
```

## 📋 Labels

| Label | Purpose |
|-------|---------|
| `automated-qa` | All automated issues |
| `production-alert` | Production problems |
| `test-coverage` | Missing tests |
| `security` | Security vulnerabilities |
| `dependencies` | Dependency updates |

## 🔔 Alert Priorities

- 🔴 **P0 Critical**: Production down, security breach
- 🟠 **P1 High**: Multiple failures, major bugs
- 🟡 **P2 Medium**: Single failure, minor issues
- 🟢 **P3 Low**: Warnings, recommendations

## 📞 Need Help?

1. Check [Automation Guide](AUTOMATION_QA_GUIDE.md)
2. Check [Runbook](AUTOMATION_RUNBOOK.md)
3. Search [existing issues](../../issues?q=label%3Aautomated-qa)
4. Create [new issue](../../issues/new) with `help-wanted` label

## 🎓 Best Practices

✅ **DO:**
- Run local tests before pushing
- Follow conventional commit format
- Review automated issues promptly
- Keep dependencies updated

❌ **DON'T:**
- Ignore automated issues
- Skip code quality checks
- Push without local testing
- Close issues without fixing

## 🔧 Manual Testing

```bash
# Interactive test runner
./scripts/qa-manual-runner.sh

# Options:
# 1) Run All Tests
# 2) Run E2E Tests Only
# 3) Run API Health Check
# 4) Run Performance Audit
# 5) Run Security Scan
# 6) Check Code Quality
# 7) Check Dependencies
# 8) View QA Status
# 9) Generate Health Report
```

## 📈 Metrics to Watch

- **Test Success Rate**: Should be 100%
- **Mean Time to Detection**: < 1 hour
- **Mean Time to Resolution**: < 4 hours
- **Test Coverage**: > 80%
- **Build Size**: < 5MB
- **Response Time**: < 3s (frontend), < 2s (backend)

## 🔄 Weekly Checklist

- [ ] Review dependency update PRs
- [ ] Close resolved automated issues
- [ ] Check health log trends
- [ ] Review security alerts
- [ ] Update documentation

## 📅 Monthly Checklist

- [ ] Review overall metrics
- [ ] Optimize slow tests
- [ ] Security audit review
- [ ] Update runbooks
- [ ] Team feedback session

---

**Quick Access:** Bookmark this page for instant reference!
