<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App;
use Auth;
use Session;

final readonly class ChangeLocale
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): array
    {
        $available_locales = config('app.available_locales');
        $locale = $args['input']['locale'] ?? 'en';

        if (in_array($locale, $available_locales)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        } else {
            Session::put('locale', 'en');
            App::setLocale('en');
        }

        return [
            'success' => true,
            'message' => [
                trans('public.successfully_changed_language')
            ],
        ];
    }

    public function getLocale(): array
    {
        return [
            'success' => true,
            'locale' => App::getLocale(),
        ];
    }
}
