#!/bin/bash

# Script de déploiement - Kabalana
# Usage : ./deploy.sh

set -e  # Arrête le script si une commande échoue

echo "🚀 Début du déploiement..."
echo ""

echo "📥 Récupération des derniers changements..."
git pull origin main

echo ""
echo "📦 Vérification des dépendances Composer..."
composer install --no-dev --optimize-autoloader

echo ""
echo "🗄️  Application des migrations Doctrine..."
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

echo ""
read -p "🎨 Recompiler les assets CSS/JS ? (o/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Oo]$ ]]
then
    php bin/console asset-map:compile --env=prod
fi

echo ""
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear --env=prod

echo ""
echo "✅ Déploiement terminé avec succès !"