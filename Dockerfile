#
FROM php:8.0-alpine

WORKDIR /appdir
COPY . /appdir

RUN composer install --no-progress \
  && comoser clearcache \

ENTRYPOINT ["php", "bin/kite"]
