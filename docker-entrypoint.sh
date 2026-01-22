#!/bin/bash
set -e

# 1. Génération des clés JWT avec la commande Symfony (plus fiable qu'openssl pur)
if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    # Utilise les variables d'env JWT_PASSPHRASE déjà présentes
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    chown -R www-data:www-data config/jwt
    chmod 600 config/jwt/private.pem
    chmod 644 config/jwt/public.pem
    echo "JWT keys generated."
fi

# 2. FIX CRUCIAL : Forcer les permissions sur le dossier var
# Cela règle l'erreur "Permission denied" sur le cache system et app
mkdir -p var/cache var/log var/sessions
chown -R www-data:www-data var
chmod -R 777 var

# 3. Gestion du cache en tant qu'utilisateur www-data
# On évite que root ne recrée des fichiers protégés
sudo -u www-data php bin/console cache:clear --env=prod

# 4. Lancement d'Apache
exec apache2-foreground
