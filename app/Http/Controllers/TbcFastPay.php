 

// namespace App\Http\Controllers\API;


// use App\Models\User;

// use App\Models\Device;

// use App\Models\Company;

// use App\Models\DeviceUser;

// use Illuminate\Http\Request;
 
// use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\Controller;
// use App\Providers\TransactionProvider;
// use App\Services\TransactionHandlerForOpMode;
// use Symfony\Component\HttpFoundation\Response;

// class TbcFastPay extends Controller
// {




//   use TransactionHandlerForOpMode;

//   use TransactionProvider;

//   public function makeTbcFastPayOrder(Request $request)
//   {
//     $validatedData = $request->validate([
//       'phone' => 'required',
//       'order_id' => 'required',
//       'amount' => 'required',
//     ]);

//     $phone = $validatedData['phone'];
//     $order_id = $validatedData['order_id'];
//     $amount = $validatedData['amount'];

//     //  ვეძებნთ უსერს ტელეფონის ნომრით
//     $data = $request->all();
//     try {
//       $user = User::where('phone', $phone)->first();
//       // makefieldid comes from use TransactionProvider;

//       $FileId =  $this->MakeFileId($user->id);

//       // ვამოწმებ არსებობს თუ არა მსგავსი order_id ბაზაზე რომ ორჯერ არ მოხდეს დაწერა
//       $isOrderExit = $this->checkIfTransactionAlreadyHappend($order_id);
//       if ($isOrderExit) {
//         return response(['FileId' =>  $FileId, 'code' => 215], 400);
//       }

//       $this->createTransactionFastPay(
//         $amount,
//         $user->id,
//         $order_id,
//         $FileId,
//         'TBC'
//       );

//       $this->updateTransactionOrderFastPay($data, $order_id, $amount);

//       return response()->json(['FileId' =>  $FileId, 'code' => 0], 200);
//     } catch (\Exception $e) {
//       \Illuminate\Support\Facades\Log::error(
//         'Error checking user existence: ' . $e->getMessage()
//       );

//       return response()->json(
//         ['code' => 300],
//         Response::HTTP_INTERNAL_SERVER_ERROR
//       );
//     }
//   }




//   public function checkIfUserExists(Request $request)
//   {
//     $phone = $request->input('phone');

//     try {
//       $user = User::where('phone', $phone)->first();

//       if ($user) {
//         return response()->json(
//           ['phone' => $phone, 'user' => $user->name],
//           Response::HTTP_OK
//         );
//       } else {
//         return response()->json(
//           ['code' => 5],
//           Response::HTTP_NOT_FOUND
//         );
//       }
//     } catch (\Exception $e) {
//       \Illuminate\Support\Facades\Log::error(
//         'Error checking user existence: ' . $e->getMessage()
//       );

//       return response()->json(
//         ['code' => 300],
//         Response::HTTP_INTERNAL_SERVER_ERROR
//       );
//     }
//   }



//   public function updateUserData($amount, $transaction, $order_id, $isFastPay)
//   {
//     try {

//       $user = User::where('id', $transaction->user_id)
//         ->with('devices')
//         ->first();
//       $transfer_amount =  $amount;

//       $sakomisio = 0;
//       if ($isFastPay == 'e_com') {
//         $sakomisio = $transfer_amount * 0.02;
//         $sakomisio = number_format($sakomisio, 2, '.', '');
//       }
//       $user->balance =
//         intval($user->balance) + $transfer_amount - $sakomisio;
//       $user->save();
//       foreach ($user->devices as $key => $device) {
//         // handleOpModecomes from use TransactionHandlerForOpMode;

//         $this->handleOpMode($device->op_mode, $user, $device);
//       }
//     } catch (\Exception $e) {
//       \Illuminate\Support\Facades\Log::error(
//         'Error checking user existence: ' . $e->getMessage()
//       );

//       return response()->json(
//         ['code' => 300],

//       );
//     }
//   }



//   public function checkIfTransactionAlreadyHappend($order_id)
//   {
//     $order = Tbctransaction::where('order_id', $order_id)->first();

//     if ($order) {
//       return true;
//     }

//     return false;
//   }


//   public function createTransactionFastPay(
//     $amount,
//     $userId,
//     $order_id,
//     $FileId,
//     $type
//   ) {
//     Tbctransaction::create([
//       'user_id' => $userId,
//       'amount' => $amount,
//       'FileId' => $FileId,
//       'order_id' => $order_id,
//       'type' => $type,
//     ]);
//   }

//   public function updateTransactionOrderFastPay($data, $order_id, $amount)
//   {
//     try {
//       $transaction = Tbctransaction::where(
//         'order_id',
//         $order_id
//       )->first();
//       if ($transaction) {
//         $data['confirmedAmount'] = $amount;
//         //  this comes from trait
//         $this->updateUserData(
//           $data,
//           $transaction,
//           $order_id,
//           'fast_pay'
//         );
//       }
//     } catch (\Exception $e) {
//       \Illuminate\Support\Facades\Log::error(
//         'Error checking user existence: ' . $e->getMessage()
//       );

//       return response()->json(
//         ['code' => 300],
//         Response::HTTP_INTERNAL_SERVER_ERROR
//       );
//     }
//   }
// }
