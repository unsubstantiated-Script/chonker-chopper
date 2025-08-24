#!/usr/bin/env bash
set -e

echo "🛠️  Fixing Laravel Sail permissions..."

# Detect current Linux user UID and GID
UID_VAL=$(id -u)
GID_VAL=$(id -g)
echo "Detected UID=$UID_VAL, GID=$GID_VAL"

# Backup .env just in case
cp .env .env.backup
echo "Backed up .env → .env.backup"

# Update .env with UID/GID
if grep -q '^WWWUSER=' .env; then
    sed -i "s/^WWWUSER=.*/WWWUSER=$UID_VAL/" .env
else
    echo "WWWUSER=$UID_VAL" >> .env
fi

if grep -q '^WWWGROUP=' .env; then
    sed -i "s/^WWWGROUP=.*/WWWGROUP=$GID_VAL/" .env
else
    echo "WWWGROUP=$GID_VAL" >> .env
fi

echo ".env updated with UID/GID"

# Reset permissions on Laravel writable directories
echo "🔧 Fixing storage and bootstrap/cache permissions..."
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Stop and rebuild Sail
echo "♻️  Rebuilding Sail..."
./vendor/bin/sail down -v
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# Run npm install inside the container
echo "📦 Installing npm dependencies..."
./vendor/bin/sail npm install

echo "✅ Laravel Sail permissions fixed and npm dependencies installed!"
