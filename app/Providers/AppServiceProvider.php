<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
        // 全角文字の最大文字数を制限するルールを追加
        Validator::extend('max_mb_chars',function($attribute, $value, $parameters, $validator){
            $maxLength = (int) $parameters[0];
            return mb_strlen($value) <= $maxLength;
        });
    }
}
