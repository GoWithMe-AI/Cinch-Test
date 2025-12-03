@echo off
echo Setting up services...
echo.
echo Make sure you have copied .env.example to .env in each service directory!
echo.
pause

REM Install all dependencies
echo Installing all dependencies...
call npm run install:all
echo.

REM Generate app keys
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
cd catalog-service
php artisan migrate
php artisan db:seed
cd ..

cd checkout-service
php artisan migrate
cd ..

echo.
echo Setup complete! Run START.bat or 'npm start' to start services.
pause

