FROM alpine:3.14

RUN apk add --no-cache nginx

COPY build/nginx/app.conf /etc/nginx/http.d/default.conf
COPY build/nginx/nginx.conf /etc/nginx/

COPY public /app

ARG USERNAME=user
ARG UID=1000
ARG GID=1000
RUN addgroup -g $GID $USERNAME
RUN adduser -u $UID -G $USERNAME -s /bin/sh -D $USERNAME

RUN chown -R $USERNAME:$USERNAME /var/lib/nginx /var/log/nginx

USER $USERNAME

CMD ["nginx"]

EXPOSE 8080
