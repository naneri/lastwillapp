FROM nginx:alpine

# Production only — dev's docker-compose.yml bind-mounts the repo straight
# into the nginx container instead, so this image's own copy of public/ is
# never used there. In production nothing is bind-mounted, so nginx needs
# its own copy of the static files it serves directly (images, the
# favicon, etc.) alongside proxying *.php to the app container.
COPY public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
