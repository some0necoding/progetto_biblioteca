# development image
FROM nginx:latest AS dev

# production image
FROM dev AS prod

# sources
WORKDIR /usr/share/nginx/html
COPY ./app/public ./public
COPY ./app/src    ./src

# configuration
WORKDIR /etc/nginx/conf.d
COPY ./nginx/conf.d .
