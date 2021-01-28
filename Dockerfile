FROM php:7.4-alpine

FROM php:7.4-alpine

COPY --from=0 /home/worker/build ./
