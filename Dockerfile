# Cr3scix Toolkit: PHP + Apache, no database, no build step.
FROM php:8.3-apache

RUN a2enmod rewrite headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && { echo 'ServerTokens Prod'; echo 'ServerSignature Off'; } > /etc/apache2/conf-enabled/security-hardening.conf

COPY --chown=www-data:www-data . /var/www/html/
RUN rm -rf /var/www/html/tests /var/www/html/.git /var/www/html/.github /var/www/html/docs

ENV SKILLS_ENABLE_NETWORK=1 \
    SKILLS_RATE_GENERAL=60 \
    SKILLS_RATE_NETWORK=10
# Apache passes these to PHP
RUN { echo 'PassEnv SKILLS_ENABLE_NETWORK SKILLS_RATE_GENERAL SKILLS_RATE_NETWORK'; } > /etc/apache2/conf-enabled/toolkit-env.conf

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s CMD php -r 'exit(@file_get_contents("http://127.0.0.1/api/skills") ? 0 : 1);'
