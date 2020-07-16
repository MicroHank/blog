<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ServerVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'server:version {string}';
    protected $signature = 'server:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Server Version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $string = $this->argument('string') ;
        $now = date('Y-m-d H:i:s') ;
        //Cache::store('database')->put('Server_Version', $now, 10) ; // 10 minutes
        Cache::forever('Server_Version', $now) ;
        // Cache::pull('Server_Version') ; // Get and then delete
        // Cache::forget('Server_Version') ; // Delete
        // Cache::flush() ; // Remove all cache
        echo "Set Server Version Cache." ;
    }
}
