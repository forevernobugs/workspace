<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libs\XLSXWriter\XLSXWriter;

class XLSXWriterServiceProvider extends ServiceProvider {

    /**
     * 注册服务提供者
     *
     * @return void
     */
    public function register() {
        $this->app->bind('App\Libs\XLSXWriter\XLSXWriterContract', function() {
            return new XLSXWriter();
        });
    }

}
