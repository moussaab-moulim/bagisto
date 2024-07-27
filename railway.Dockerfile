# main image
FROM php:8.1-apache

# arguments
ARG CONTAINER_PROJECT_PATH
ARG CONTAINER_UID
ARG CONTAINER_USER

ARG CONTAINER_PROJECT_PATH
ARG CONTAINER_UID
ARG CONTAINER_USER
ARG APP_NAME
ARG APP_ENV
ARG APP_KEY
ARG APP_DEBUG
ARG APP_URL
ARG APP_ADMIN_URL
ARG APP_TIMEZONE
ARG APP_LOCALE
ARG APP_CURRENCY
ARG LOG_CHANNEL
ARG DB_CONNECTION
ARG DB_HOST
ARG DB_PORT
ARG DB_DATABASE
ARG DB_USERNAME
ARG DB_PASSWORD
ARG DB_PREFIX
ARG BROADCAST_DRIVER
ARG CACHE_DRIVER
ARG SESSION_DRIVER
ARG SESSION_LIFETIME
ARG QUEUE_DRIVER
ARG REDIS_HOST
ARG REDIS_PASSWORD
ARG REDIS_PORT
ARG MAIL_MAILER
ARG MAIL_HOST
ARG MAIL_PORT
ARG MAIL_USERNAME
ARG MAIL_PASSWORD
ARG MAIL_ENCRYPTION
ARG MAIL_FROM_ADDRESS
ARG MAIL_FROM_NAME
ARG ADMIN_MAIL_ADDRESS
ARG ADMIN_MAIL_NAME
ARG FIXER_API_KEY
ARG EXCHANGE_RATES_API_KEY
ARG PUSHER_APP_ID
ARG PUSHER_APP_KEY
ARG PUSHER_APP_SECRET
ARG PUSHER_APP_CLUSTER
ARG MIX_PUSHER_APP_KEY
ARG MIX_PUSHER_APP_CLUSTER
ARG FACEBOOK_CLIENT_ID
ARG FACEBOOK_CLIENT_SECRET
ARG FACEBOOK_CALLBACK_URL
ARG TWITTER_CLIENT_ID
ARG TWITTER_CLIENT_SECRET
ARG TWITTER_CALLBACK_URL
ARG GOOGLE_CLIENT_ID
ARG GOOGLE_CLIENT_SECRET
ARG GOOGLE_CALLBACK_URL
ARG LINKEDIN_CLIENT_ID
ARG LINKEDIN_CLIENT_SECRET
ARG LINKEDIN_CALLBACK_URL
ARG GITHUB_CLIENT_ID
ARG GITHUB_CLIENT_SECRET
ARG GITHUB_CALLBACK_URL
ARG ELASTICSEARCH_HOST

# installing dependencies
RUN apt-get update && apt-get install -y \
    git \
    ffmpeg \
    libfreetype6-dev \
    libicu-dev \
    libgmp-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libzip-dev \
    unzip \
    zlib1g-dev

# configuring php extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-configure intl

# installing php extension
RUN docker-php-ext-install bcmath calendar exif gd gmp intl mysqli pdo pdo_mysql zip

# installing composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# installing node js
COPY --from=node:latest /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node:latest /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# installing global node dependencies
RUN npm install -g npx
RUN npm install -g laravel-echo-server

# setting work directory
WORKDIR $CONTAINER_PROJECT_PATH

# adding user
RUN useradd -G www-data,root -u $CONTAINER_UID -d /home/$CONTAINER_USER $CONTAINER_USER
RUN mkdir -p /home/$CONTAINER_USER/.composer && \
    chown -R $CONTAINER_USER:$CONTAINER_USER /home/$CONTAINER_USER

# setting apache
COPY ./.railway.config/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

COPY ./wait-for-it.sh /opt/wait-for-it.sh
RUN chmod +x /opt/wait-for-it.sh
RUN sed -i 's/\r//g' /opt/wait-for-it.sh

# setting up project from `src` folder
RUN chmod -R 775 $CONTAINER_PROJECT_PATH
RUN chown -R $CONTAINER_USER:www-data $CONTAINER_PROJECT_PATH

# changing user
USER $CONTAINER_USER

RUN /opt/wait-for-it.sh ${DB_HOST}:${DB_PORT}
RUN composer install

RUN php artisan optimize:clear
RUN php artisan migrate:fresh --seed
RUN php artisan storage:link
RUN php artisan bagisto:publish --force
RUN php artisan optimize:clear
