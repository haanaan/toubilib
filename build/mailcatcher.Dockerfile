FROM ruby:3.3

RUN bundle config --global frozen 1

WORKDIR /usr/src/app

RUN gem install mailcatcher
RUN gem install sqlite3
EXPOSE 1025 1080
CMD ["mailcatcher", "--smtp-ip=0.0.0.0", "--smtp-port=1025", "--http-ip=0.0.0.0", "--http-port=1080", "--foreground"]