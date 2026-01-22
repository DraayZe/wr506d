#!/bin/bash
set -e

# Generate JWT keys if they don't exist
if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:${JWT_PASSPHRASE}
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:${JWT_PASSPHRASE}
    chown -R www-data:www-data config/jwt
    chmod 644 config/jwt/private.pem config/jwt/public.pem
    echo "JWT keys generated."
fi

# Clear cache
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Start Apache
exec apache2-foreground
