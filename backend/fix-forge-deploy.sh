#!/bin/bash

echo "🚀 Fixare Deploy Forge - Unmerged Files"
echo "========================================="

# Navigare la directorul proiectului
cd /home/forge/renthub-tbj7yxj7.on-forge.com || exit 1

echo "📍 Director curent: $(pwd)"

# Verificare status git
echo "📊 Status Git curent:"
git status --porcelain

# Resetare la ultimul commit curat
echo "🔄 Resetare la ultimul commit curat..."
git reset --hard HEAD
git clean -df

# Verificare din nou
echo "📊 Status după reset:"
git status --porcelain

# Pull forțat de pe master
echo "📥 Pull de pe branch-ul master..."
git fetch origin
git reset --hard origin/master

# Verificare finală
echo "✅ Status final:"
git status --porcelain
echo "🔄 Log commit-uri recente:"
git log --oneline -5

echo ""
echo "✅ Proces complet! Serverul este pregătit pentru deploy."
echo "🚀 Poți acum să declanșezi deploy-ul din panoul Forge."