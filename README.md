![Screenshot](https://github.com/tomatophp/laravel-agora/blob/master/art/screenshot.png)

# Laravel Agora

Agora Token Generator With easy to use Service Class

⭐ **If this package saves you time, star it** and browse 60+ more Filament plugins at [tomatophp.com/en/plugins](https://tomatophp.com/en/plugins).

## Installation

```bash
composer require tomatophp/laravel-agora
```

## Configuration

Add your Agora credentials (from the [Agora console](https://console.agora.io)) to `.env`:

```dotenv
AGORA_APP_ID=your-app-id
AGORA_APP_CERTIFICATE=your-app-certificate
# optional: how long a token stays valid, in seconds (default 3600 = 1 hour)
AGORA_TOKEN_EXPIRE=3600
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=laravel-agora-config
```

## Usage

Use the `Agora` service anywhere in your own classes (controllers, actions, jobs, models...) to generate an RTC token for a user and a call/channel:

```php
use TomatoPHP\LaravelAgora\Services\Agora;

class CallController extends Controller
{
    public function token(Call $call)
    {
        $agora = Agora::make($call->id)      // unique id of the call/room
            ->channel('call')                // channel prefix, the final channel name is "call.{id}"
            ->uId((string) auth()->id());    // the Agora uid of the current user

        return response()->json([
            'app_id'  => config('laravel-agora.agora.app_id'),
            'channel' => 'call.' . $call->id,
            'uid'     => (string) auth()->id(),
            'token'   => $agora->token(),
        ]);
    }
}
```

Available methods:

| Method | Description |
|---|---|
| `Agora::make($id)` | Start a token for the given room/call id |
| `->channel('name')` | Channel prefix (default `agora`); the channel name is `{channel}.{id}` |
| `->uId('123')` | Agora uid of the user the token is for |
| `->join()` | Generate a subscriber (audience) token instead of a publisher token |
| `->audioOnly()` | Audio-only token |
| `->expireIn(7200)` | Token lifetime in seconds for this token (overrides `AGORA_TOKEN_EXPIRE`) |
| `->token()` | Build and return the token string |

Pass the `app_id`, the channel name, the uid and the token to the Agora SDK on your client (web, Flutter, mobile).

### Changing the token expiry

By default a token is valid for one hour. You can change the default for the whole app with `AGORA_TOKEN_EXPIRE` (seconds), or per token:

```php
$token = Agora::make($call->id)
    ->channel('call')
    ->uId((string) auth()->id())
    ->expireIn(60 * 60 * 24) // 24 hours
    ->token();
```

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/VZc8nBJ3ZU)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/plugins/laravel-agora)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](mailto:info@3x1.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## More open source from the same author

- [TomatoPHP](https://tomatophp.com): 60+ open-source FilamentPHP plugins for Laravel
- [Laravilt](https://laravilt.com): Laravel admin panels rendered with Inertia on Vue 3 or React 19
- [ToGO](https://to-go.dev): the Laravel-style workflow for Go + React apps, with a plugin marketplace
- [Zekra](https://zekra.dev): long-term memory for AI agents over MCP

Built by [Fady Mondy](https://fadymondy.com).
