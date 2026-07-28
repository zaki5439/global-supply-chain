FROM ubuntu:22.04

# Prevent interactive prompts during apt install
ENV DEBIAN_FRONTEND=noninteractive

# Update and install system dependencies
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    gettext-base \
    supervisor \
    redis-server \
    nginx \
    python3.10 \
    python3-pip \
    python3-venv \
    build-essential libpq-dev python3-dev \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update && apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-redis \
    && rm -rf /var/lib/apt/lists/*

# Setup PHP-FPM socket directory
RUN mkdir -p /run/php

# Set working directory
WORKDIR /app

# Copy python dependencies
COPY requirements.txt .
RUN pip3 install --upgrade pip && pip3 install --no-cache-dir setuptools wheel && pip3 install --no-cache-dir -r requirements.txt

# Copy all application files
COPY . .

# Copy configurations
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY nginx.conf /etc/nginx/sites-available/default

# Remove default nginx index
RUN rm -rf /var/www/html

# Script to start services (Replaces ${PORT} in nginx config)
RUN echo '#!/bin/bash\n\
if [ -z "$PORT" ]; then\n\
  export PORT=8080\n\
fi\n\
envsubst "\\$PORT" < /etc/nginx/sites-available/default > /tmp/nginx.conf && mv /tmp/nginx.conf /etc/nginx/sites-available/default\n\
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /app/start.sh && chmod +x /app/start.sh

# Expose default port
ENV PORT=8080
EXPOSE $PORT

# Start everything via supervisord
CMD ["/app/start.sh"]
