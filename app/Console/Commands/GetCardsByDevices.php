<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Company;
use App\Models\Device;
use Illuminate\Console\Command;

class GetCardsByDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:get-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get cards by users associated with devices only by IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companies = Company::where('isBlocked',false)->get();
        foreach ($companies as $key => $company) {
            $devices = Device::where('company_id',$company->id)
                ->with('users')
                ->get();
            foreach ($devices->users as $i => $user) {
                $cards = Card::where('user_id', $user->id)
                    ->where('has_paid' ,'<', time())->get();
                foreach ($cards as $b => $card){
                    if($user->balance - $user->freezed_balance > $company->card) {

                    }
                }
            }
        }
    }
}
