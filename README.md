# 7span Demo

## Installation steps


1. Clone the repository
```
git clone https://github.com/imujas009/7span-demo.git
```
> Regarding branch : Possibly `dev` branch in repository has latest code. Please change the branch just after cloning repository.

2. Install required php dependency
```
composer install
```

3. Create .env file from .env.example and fill up required parameters
```
cp .env.example .env
```

4. Laravel cache clear
```
php artisan config:cache
```

5. Install Passport
```
php artisan passport:install
```

6. Remove all records from database and start with new details
```
php artisan migrate:fresh --seed
```
