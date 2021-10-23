FROM nginx:1.21-alpine

RUN rm -rf docker-entrypoint.sh docker-entrypoint.d

COPY build/nginx/nginx.conf /nginx/nginx.conf
COPY build/nginx/app.conf /nginx/app.conf.template
RUN mkdir -p /nginx/cache/proxy_temp \
    /nginx/cache/fastcgi_temp \
    /nginx/cache/uwsgi_temp \
    /nginx/cache/scgi_temp \
    && chmod -R 777 /nginx

COPY public /app

COPY build/nginx/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT [ "/entrypoint.sh" ]
CMD [ "nginx", "-c", "/nginx/nginx.conf" ]

EXPOSE 8080
