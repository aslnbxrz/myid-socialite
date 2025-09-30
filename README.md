# MyID for Laravel Socialite

MyID provider for [SocialiteProviders](https://github.com/SocialiteProviders/Providers).

Docs: [MyID Web SDK](https://docs.myid.uz/#/ru/websdk)

## Requirements

- PHP 8.1+
- Laravel 10/11+
- `laravel/socialite`
- `socialiteproviders/manager`

## Installation

```bash
composer require aslnbxrz/myid-socialite
```

## Configuration

Add to `config/services.php`:

```php
'myid' => [
    'client_id'     => env('MYID_CLIENT_ID'),
    'client_secret' => env('MYID_CLIENT_SECRET'),
    'redirect'      => env('MYID_REDIRECT_URI'),
    // Optional (defaults shown):
    'base_url'      => env('MYID_BASE_URL', 'https://myid.uz'),
    'scope'         => env('MYID_SCOPE', 'openid profile email'),
],
```

Add to your `.env`

```dotenv
MYID_CLIENT_ID=your-client-id
MYID_CLIENT_SECRET=your-client-secret
MYID_REDIRECT_URI=https://your-app.com/auth/myid/callback
# Optional:
# MYID_BASE_URL=https://myid.uz
# MYID_SCOPE="openid profile email"
```

## Laravel 11+ Event Listener

```php
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Aslnbxrz\MyID\Provider;

Event::listen(function (SocialiteWasCalled $event) {
    $event->extendSocialite('myid', Provider::class);
});
```

## Usage

```php
use Laravel\Socialite\Facades\Socialite;

// Redirect to MyID
Route::get('/auth/myid/redirect', function () {
    return Socialite::driver('myid')->scopes(['openid','profile','email'])->redirect();
});

// Callback
Route::get('/auth/myid/callback', function () {
    /** @var \Aslnbxrz\MyID\MyIDUser $user */
    $user = Socialite::driver('myid')->user();

    $id    = $user->getId();
    $name  = $user->getName();
    $email = $user->getEmail();
    $phone = $user->getPhone();
});
```

## Endpoints

- Authorize: `https://myid.uz/oauth/authorize`
- Token: `https://myid.uz/oauth/token`
- Userinfo: `https://myid.uz/oauth/userinfo`

---

## License

MIT


