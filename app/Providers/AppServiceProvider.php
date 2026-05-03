<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        Vite::createAssetPathsUsing(fn (string $path) => '/' . ltrim($path, '/'));

        RateLimiter::for('gemini-insight', function (Request $request) {
            $key = session('firebase_uid') ?: $request->ip();

            return Limit::perMinute(5)
                ->by('gemini-insight:' . $key)
                ->response(function (Request $request, array $headers) use ($key) {
                    Log::warning('Gemini insight rate limit reached.', [
                        'key' => $key,
                        'ip' => $request->ip(),
                        'history_id' => $request->route('historyId'),
                    ]);

                    return response()->json([
                        'message' => 'AI limit reached. Showing local fallback insight.',
                    ], 429, $headers);
                });
        });
    }
}
