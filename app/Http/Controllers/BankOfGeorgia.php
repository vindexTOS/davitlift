<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class BankOfGeorgia extends Controller
{
 
//  unda gamoviyenot get methodebi
// hash kodi  HASH_CODE
// Parameter	Function
// OP	Operation to be carried out. 4 operations defined: 
// 1.	debt – displaying debt
// 2.	verify – verifying availability of payment 
// 3.	pay – payment 
// 4.	ping – inspecting the service 
// USERNAME	Username. Needed to identify the iPay system at the Service-provider 
// PASSWORD	User password. Needed to identify the iPay system at the Service-provider
// CUSTOMER_ID	User identifier. Assigned by the Service-provider
// SERVICE_ID	Service identifier. In some cases may be empty, e.g. “dsl” or “voip” or anything else. Values are determined by the Service-provider. 
// PAY_SRC	Source of payment. The iPay system allows payments from several sites: ATM, web-site, Direct Debit Georgia payment stations, etc. 
// PAY_AMOUNT	Payment amount in Tetri. 
// PAYMENT_ID	Unique payment identifier.
// EXTRA_INFO	Additional payment information encoded in URLEncode format.
// verify END POINT NAME


// კოდი	დანიშნულება
// 0	OK
// 1	წვდომა შეზღუდულია (მაგ. მოცემული IP მისამართიდან სერვისის გამოძახება არ შეიძლება და ა.შ.)
// 2	მომხმარებელი/პაროლი არასწორია
// 3	ჰაშ-კოდი არასწორია
// 4	აუცილებელი პარამეტრი აკლია (მაგალითად, გადახდისას PAYMENT_ID პარამეტრი არ იყო მითითებული)
// 5	პარამეტრის მნიშვნელობა არასწორია (მაგ. ჰეშ-კოდში 16-ობითი რიცხვი არ არის გადმოცემული)
// 6	მომხმარებელი არ მოიძებნა
// 7	თანხა არასწორია
// 8	გადახდის იდენტიფიკატორი (PAYMENT_ID) არ არის უნიკალური
// 9	გადახდა შეუძლებელია
// 10	სერვისი არ არსებობს (სერვისის იდენტიფიკატორი გადაეცემა SERVICE_ID-ში)
// 18	გადახდამ ჩაიარა წარმატებით მიუხედავად იმისა რომ გადახდის იდენტიფიკატორი (PAYMENT_ID) არ არის უნიკალური. ეს კოდი რეზერვირებულია სპეციალური შემთხვევებისათვის და წინასწარი შეთანხმების გარეშე სერვის-პროვაიდერის მხრიდან მისმა დაბრუნებამ შეიძლება გამოიწვიოს გაუთვალისწინებელი შედეგები
// 99	საერთო შეცდომა
public function checkIfUserExists(Request $request)
{
    $phone = $request->input('phone');
    
    try {
        $user = User::where('phone', $phone)->first();
        
        if ($user) {
            return response()->json(
                ['phone' => $phone, 'user' => $user->name],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['code' => 5],
                Response::HTTP_NOT_FOUND
            );
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error(
            'Error checking user existence: ' . $e->getMessage()
        );
        
        return response()->json(
            ['code' => 300],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}

}
