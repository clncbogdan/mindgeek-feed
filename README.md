
# MGFeed for localhost

## Prerequisites:

- Composer ([download](https://getcomposer.org/download/))
- PHP (>=8.0.1)
- Docker

## How to run

- Clone the project `git clone
  https://github.com/clncbogdan/mindgeek-feed.git`
- In a terminal inside the folder run `cp .env-example .env`
- To build the containers, run from the terminal `docker compose up` (it might take a while the first time you run it)
- Open another terminal inside the folder and run `php artisan migrate:fresh`
- After the migration is finished, run `php artisan mindgeek:import` to import the files from the feed
- The application should be available at [http://localhost/api/movies](http://localhost/api/movies)



## Running tests:

- Open a terminal inside the folder and run `php artisan test`
