<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction; // Import the Transaction model
use App\Http\Controllers\API\TransactionController; // Import the TransactionController

class TransactionDetailCron extends Command
{
    protected $signature = 'transaction:details';
    protected $description = 'Get Transaction Details for Created and Pending Transactions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $transactions = Transaction::where('status', 'Created')->orWhere('status','Processing')->get();

        $controller = new TransactionController();

        foreach ($transactions as $transaction) {
            $controller->getTransactionDetail($transaction->transaction_id);
        }

        $this->info('Transaction details have been fetched successfully.');
    }
}
