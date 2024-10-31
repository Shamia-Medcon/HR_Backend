<?php

namespace App\Providers;

use App\Services\Facades\FBase;

use App\Services\Facades\FLeave;
use App\Services\Facades\FTransfer;
use App\Services\Facades\FType;
use App\Services\Facades\FUser;
use App\Services\Interfaces\IBase;
use App\Services\Interfaces\ILeave;
use App\Services\Interfaces\ITransfer;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(IBase::class, FBase::class);
        $this->app->singleton(IUser::class, FUser::class);
        $this->app->singleton(IType::class, FType::class);
        $this->app->singleton(ILeave::class, FLeave::class);
        $this->app->singleton(ITransfer::class, FTransfer::class);
    }
}
