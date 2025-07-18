FROM webdevops/php-nginx:8.4

WORKDIR /var/www/html

ENV WEB_DOCUMENT_ROOT /var/www/html/public

# Timeout for the post update script in minutes.
LABEL com.centurylinklabs.watchtower.lifecycle.post-update-timeout="1440"
# Watchtower will run this script after restarting the updated container.
LABEL com.centurylinklabs.watchtower.lifecycle.post-update="/var/www/html/docker/shared/watchtower/post-update.sh"

COPY composer.json composer.lock /var/www/html/
RUN composer install --no-interaction --no-dev --no-scripts

COPY --chown=application:application . /var/www/html

RUN mkdir -p /var/www/html/storage
RUN chmod 755 /var/www/html/docker/webdevops/entryfile.sh /var/www/html/docker/shared/watchtower/post-update.sh /var/www/html/storage

RUN apt update
RUN apt install -y default-mysql-client

ENTRYPOINT ["/var/www/html/docker/webdevops/entryfile.sh"]
