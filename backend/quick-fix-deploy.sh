#!/bin/bash

echo "🚀 REZOLVARE RAPIDĂ - FORGE & VERCEL PROBLEME"
echo "=============================================="

# REZOLVARE IMEDIATĂ FORGE
echo ""
echo "⚡ REZOLVARE FORGE - COMANDĂ RAPIDĂ:"
echo "ssh forge@renthub-tbj7yxj7.on-forge.com 'cd /home/forge/renthub-tbj7yxj7.on-forge.com && git reset --hard HEAD && git clean -df && git pull origin master && php artisan migrate --force && php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000 --daemon'"

echo ""
echo "🔧 ALTERNATIVĂ - Reset din panoul Forge:"
echo "1. Loghează-te în Laravel Forge"
echo "2. Selectează site-ul RentHub"
echo "3. Click pe 'Meta' tab"
echo "4. Click pe 'Reset Git State'"
echo "5. Click pe 'Deploy Now'"

echo ""
echo "⚡ REZOLVARE VERCEL - COMENZI RAPIDE:"
echo "cd ../frontend"
echo "npm install"
echo "npm run build"
echo "vercel --prod"

echo ""
echo "🎯 VERIFICARE FINALĂ:"
echo "Backend: curl https://renthub-tbj7yxj7.on-forge.com/api/health"
echo "Frontend: Deschide https://renthub-frontend.vercel.app"

echo ""
echo "📞 DACĂ PROBLEMELE PERSISTĂ:"
echo "1. Verifică logs în Laravel Forge (Meta > Logs)"
echo "2. Verifică logs în Vercel (Deployments > View Logs)"
echo "3. Rulează scriptul complet: ./fix-deploy-complete.sh"

echo ""
echo "✅ EXECUTĂ COMANDA DE MAI SUS PENTRU REZOLVARE IMEDIATĂ!"