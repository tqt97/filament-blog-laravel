### Step to build this project

1. Install Filament Admin

```
    composer require filament/filament:"^3.0-stable" -W
    php artisan filament:install --panels
    php artisan make:filament-user
    php artisan vendor:publish --tag=filament-config
    php artisan vendor:publish --tag=filament-panels-translations
```

-   Note: restrict users can access your panel

```php
    <?php

    namespace App\Models;

    use Filament\Models\Contracts\FilamentUser;
    use Filament\Panel;
    use Illuminate\Foundation\Auth\User as Authenticatable;

    class User extends Authenticatable implements FilamentUser
    {
        // ...

        public function canAccessPanel(Panel $panel): bool
        {
            return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
        }
    }
```
