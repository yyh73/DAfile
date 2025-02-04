<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;//追記

class AppServiceProvider extends ServiceProvider
{
   public function boot(): void
   {
      Paginator::useBootstrap();//追記


      //Paginator::useBootstrapFive(); 公式ドキュメント
      //または Paginator::useBootstrapFour(); 公式ドキュメント
   }



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
    
}
