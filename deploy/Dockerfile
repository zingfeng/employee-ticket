FROM gitlab.f6.com.vn:4567/namtq/nginx-phpfpm-env:latest 
WORKDIR /var/www/html
COPY composer.json composer.json
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader -vvv
RUN rm -rf /root/.composer
# Copy codebase 
COPY --chown=nginx:nginx . ./
RUN cat ./deploy/config/supervisord.conf >> /etc/supervisor/conf.d/supervisord.conf
# Finish composer
RUN composer dump-autoload --no-dev --optimize -vvv
RUN chown -R nginx:nginx *
USER nginx