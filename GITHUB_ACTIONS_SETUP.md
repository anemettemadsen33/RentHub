# RentHub - GitHub Actions Setup Guide

## 🚀 GitHub Actions Created

Am creat 3 workflow-uri automate:

### 1. **Deploy Pipeline** (`.github/workflows/deploy.yml`)
- **Trigger**: Push pe master/main, Pull Requests
- **Jobs**:
  - Code Analysis (ESLint, TypeScript)
  - Build Frontend
  - Deploy to Vercel (Preview pentru PR, Production pentru master)

### 2. **Auto-Fix** (`.github/workflows/auto-fix.yml`)
- **Trigger**: Daily la 2 AM sau manual
- **Funcții**:
  - Auto-fix ESLint issues
  - Format cu Prettier
  - Creează automat Pull Request cu fix-urile

### 3. **Quality Checks** (`.github/workflows/quality.yml`)
- **Trigger**: Push, Pull Requests
- **Verificări**:
  - Run tests
  - Check unused dependencies
  - Security audit
  - Bundle size analysis

---

## 📋 Setup Necesar

### Pasul 1: Adaugă Secrets în GitHub

Du-te la: `Settings` → `Secrets and variables` → `Actions` → `New repository secret`

Adaugă următoarele secrets:

```
VERCEL_TOKEN - Token de la Vercel (Settings → Tokens)
VERCEL_ORG_ID - ID-ul organizației Vercel
VERCEL_PROJECT_ID - ID-ul proiectului rent-hub
```

#### Cum obții Vercel secrets:

1. **VERCEL_TOKEN**:
   - https://vercel.com/account/tokens
   - Create Token → Copy

2. **VERCEL_ORG_ID** și **VERCEL_PROJECT_ID**:
   ```bash
   cd frontend
   npx vercel link
   ```
   - Urmează pașii
   - Caută în `.vercel/project.json`:
   ```json
   {
     "orgId": "...",
     "projectId": "..."
   }
   ```

---

## 🎯 Cum Funcționează

### Workflow Automat:

1. **Push la GitHub** → Actions se declanșează automat
2. **Code Analysis** → Verifică ESLint, TypeScript
3. **Build** → Compilează aplicația
4. **Deploy** → Deploy automat pe Vercel
5. **Verificare** → Testează deployment-ul

### Deploy Manual:

```bash
# Trigger manual deploy
gh workflow run deploy.yml

# Trigger auto-fix
gh workflow run auto-fix.yml
```

---

## 🔧 Configurare Locală

### Instalare GitHub CLI:
```bash
winget install GitHub.cli
```

### Link repository:
```bash
gh repo view
gh workflow list
gh workflow run deploy.yml
```

---

## 📊 Status Badges

Adaugă în README.md:

```markdown
![Deploy](https://github.com/anemettemadsen33/RentHub/workflows/Vercel%20Deploy%20&%20Test/badge.svg)
![Quality](https://github.com/anemettemadsen33/RentHub/workflows/Code%20Quality%20Checks/badge.svg)
```

---

## 🎉 Beneficii

✅ **Deploy Automat** - Push → Auto-deploy  
✅ **Quality Gates** - Verificări automate înainte de merge  
✅ **Auto-fix** - Bot care repară probleme comune  
✅ **Preview Deployments** - Preview pentru fiecare PR  
✅ **Security Checks** - Audit automat de securitate  

---

## 🚨 Troubleshooting

### Dacă workflow-ul eșuează:

1. **Check logs**: Actions tab → Click pe workflow → View logs
2. **Verifică secrets**: Settings → Secrets → Toate sunt setate?
3. **Re-run**: Click pe workflow → Re-run all jobs

### Common Issues:

- **Vercel token invalid**: Regenerează token în Vercel
- **Build fails**: Check build logs în Actions
- **Tests fail**: Fix tests local apoi push

---

## 📞 Next Steps

După setup:

1. **Adaugă secrets** în GitHub
2. **Push codul** → Workflow se va rula automat
3. **Verifică** Actions tab pentru status
4. **Monitorizează** deployments în Vercel

---

**Status**: Workflows created, waiting for secrets setup
