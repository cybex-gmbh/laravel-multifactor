FROM webdevops/php-nginx:8.4

WORKDIR /var/www/html/

ENV WEB_DOCUMENT_ROOT /var/www/html/public

# Timeout for the post update script in minutes.
LABEL com.centurylinklabs.watchtower.lifecycle.post-update-timeout="1440"
# Watchtower will run this script after restarting the updated container.
LABEL com.centurylinklabs.watchtower.lifecycle.post-update="/var/www/html/docker/shared/watchtower/post-update.sh"

COPY . ../package
COPY exampleApp/composer.json exampleApp/composer.lock ./
RUN composer install --no-interaction --no-dev --no-scripts

COPY --chown=application:application exampleApp ./

RUN chmod 755 ./docker/webdevops/entryfile.sh ./storage

RUN apt update
RUN apt install -y default-mysql-client

ENTRYPOINT ["/var/www/html/docker/webdevops/entryfile.sh"]
