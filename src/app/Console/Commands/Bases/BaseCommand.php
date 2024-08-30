<?php
namespace App\Console\Commands\Bases;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BaseCommand extends Command
{
    protected $signature="batch:base";
    protected $description="description";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        Log::setDefaultDriver('batch');
        parent::__construct();
    }


    public function handle()
    {
    }

    protected function preHandle(){
        Log::info('--------------- '.get_class($this).' START!!! -------------------');
    }


    protected function postHandle(){
        Log::info('--------------- '.get_class($this).' END!!! -------------------');
    }
}