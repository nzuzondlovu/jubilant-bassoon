<?php

namespace App\Console\Commands;

use App\Http\Controllers\LottoController;
use Exception;
use Illuminate\Console\Command;

class LottoDraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lotto-draw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the winning numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Begin processing of lotto winnings');

            $lotto = new LottoController();
            $lotto->process();

            $this->info('Completed processing of lotto winnings');
        } catch (Exception $e) {
            report($e);
            $this->error('Something went wrong!');
        }
    }
}
