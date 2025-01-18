FROM postgres:latest

ENV POSTGRES_PASSWORD=docker
ENV POSTGRES_DB=db
ENV POSTGRES_USER=docker

# Kopiowanie opcjonalnego skryptu inicjalizacyjnego
COPY ./init.sql /docker-entrypoint-initdb.d/
