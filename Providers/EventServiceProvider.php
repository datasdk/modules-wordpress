<?php

namespace Modules\Wordpress\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    /**
     * Event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */




    protected $listen = [
    
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
