# Deployment Instructions

Install dependencies:
<pre>composer install</pre>

Create a .env file:
<pre>
cp .env.example .env
</pre>

Configure database settings in the the .env file:
<pre>
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
</pre>

Run migrations and seed the database:
<pre>
php artisan migrate
php artisan db:seed
</pre>

By default, the seeder will create two users:
<pre>
login: 'example@example.com', password: 'password'
login: 'another@example.com', password: 'password'
</pre>
