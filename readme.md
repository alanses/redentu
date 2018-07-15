How do I get set up?
Installation:

install docker and docker-compose
$ docker-compose up -d
$ npm i && npm watch
Create .env file from source
$ docker-compose exec php-fpm composer install
$ docker-compose exec php-fpm php artisan key:gen
$ docker-compose exec php-fpm php artisan migrate
Done...

Vuejs commands (webpack)
Vue app build
npm run dev (development build mode)
npm run watch (live watcher)
npm run production (production build mode)
npm run hot (hot reload)