@echo off
echo Setting up services...
echo.

REM Check and create .env files from .env.example for each service
echo Checking .env files...
echo.

REM Catalog Service
if not exist "catalog-service\.env" (
    echo Creating .env file for catalog-service...
    if exist "catalog-service\.env.example" (
        copy "catalog-service\.env.example" "catalog-service\.env" >nul
        echo   - Created catalog-service\.env from .env.example
    ) else (
        echo   - Warning: .env.example not found in catalog-service, creating empty .env
        type nul > "catalog-service\.env"
    )
) else (
    echo   - catalog-service\.env already exists, skipping...
)

REM Checkout Service
if not exist "checkout-service\.env" (
    echo Creating .env file for checkout-service...
    if exist "checkout-service\.env.example" (
        copy "checkout-service\.env.example" "checkout-service\.env" >nul
        echo   - Created checkout-service\.env from .env.example
    ) else (
        echo   - Warning: .env.example not found in checkout-service, creating empty .env
        type nul > "checkout-service\.env"
    )
) else (
    echo   - checkout-service\.env already exists, skipping...
)

REM Email Service
if not exist "email-service\.env" (
    echo Creating .env file for email-service...
    if exist "email-service\.env.example" (
        copy "email-service\.env.example" "email-service\.env" >nul
        echo   - Created email-service\.env from .env.example
    ) else (
        echo   - Warning: .env.example not found in email-service, creating empty .env
        type nul > "email-service\.env"
    )
) else (
    echo   - email-service\.env already exists, skipping...
)

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

