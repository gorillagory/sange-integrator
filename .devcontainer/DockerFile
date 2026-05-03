# Use PHP 8.3 with Node 20 as the base for the Laravel/Nexus Hybrid
FROM mcr.microsoft.com/devcontainers/php:8.3-bookworm

# Install PHP extensions required by the Laravel context
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql gd zip

# Install Node.js (via NVM already in devcontainer base) and Global Nexus Tools
RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g firebase-tools @google/clasp vite"

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /workspace
