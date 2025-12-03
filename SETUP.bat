@echo off
echo Setting up services...
echo.
echo Make sure you have copied .env.example to .env in each service directory!
echo.

REM Create bootstrap/cache directories for Laravel services
echo Creating bootstrap/cache directories...
if not exist "catalog-service\bootstrap\cache" mkdir "catalog-service\bootstrap\cache"
if not exist "checkout-service\bootstrap\cache" mkdir "checkout-service\bootstrap\cache"
if not exist "email-service\bootstrap\cache" mkdir "email-service\bootstrap\cache"
echo.

REM Install all dependencies
echo Installing all dependencies...
call npm run install:all
echo.

REM Generate app keys
echo Generating application keys...
cd catalog-service
php artisan key:generate
cd ..

cd checkout-service
php artisan key:generate
cd ..

cd email-service
php artisan key:generate
cd ..

REM Run migrations and seed
echo Running database migrations...
cd catalog-service
php artisan migrate
php artisan db:seed
cd ..

cd checkout-service
php artisan migrate
cd ..

echo.
echo Setup complete! Run START.bat or 'npm start' to start services.

