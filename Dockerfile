# build:
#  docker build . -t inhere:kite
# run:
# docker run inhere:kite gh cl -h
FROM php:8.1
ENV KITE_CMD="list"

WORKDIR /appdir
COPY . /appdir

ENTRYPOINT ["php", "bin/kite"]
#CMD $KITE_CMD
