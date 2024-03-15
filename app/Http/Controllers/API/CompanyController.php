<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyTransaction;
use App\Models\Device;
use App\Models\DeviceEarn;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\DB;
class CompanyController extends Controller
{
    public function index()
    {
        $data = [
            'companies' => [],
        ];
        $data['totalEarning'] = 0;
        $data['totalCashback'] = 0;
        $data['totalServiceFee'] = 0;
        $data['payedServiceFee'] = 0;
        $data['payedCashback'] = 0;
        $data['deviceStats'] = [
            'active' => 0,
            'inactive' => 0,
        ];
        $companies = Company::with('admin')->get();
        $data['payedServiceFeeByMonths'] = CompanyTransaction::select(
            DB::raw('SUM(amount) as total_amount'),
            DB::raw("DATE_FORMAT(created_at, '%m') as month")
        )
            ->where('type', 2)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        foreach ($companies as $key => $company) {
            $statistic = $this->getStatisticFromOneCompany($company);
            $data['deviceStats']['active'] +=
                $statistic['deviceActivity']['active'];
            $data['deviceStats']['inactive'] +=
                $statistic['deviceActivity']['inactive'];
            $companyItem = [
                ...$statistic['company']->toArray(),
                'totalEarnings' => $statistic['totalEarnings'],
                'needToPay' => $statistic['needToPay'],
                'companyFee' => $statistic['companyFee'],
                'payedCompanyFee' => $statistic['payedCompanyFee'],
                'payedCashback' => $statistic['payedCashback'],
                'needToPayCompanyFee' =>
                    $statistic['companyFee'] - $statistic['payedCompanyFee'],
                'withdrewedTotalFee' => $statistic['withdrew'],
            ];
            $data['companies'][] = $companyItem;
            $data['totalEarning'] += $statistic['totalEarnings'];
            $data['totalCashback'] += $statistic['needToPay'];
            $data['totalServiceFee'] += $statistic['companyFee'];
            $data['payedServiceFee'] += $statistic['payedCompanyFee'];
            $data['payedCashback'] += $statistic['payedCashback'];
        }
        return $data;
    }
    public function blockCompanies($id)
    {
        $company = Company::where('id', $id)->first();
        $company->isBlocked = true;
        $company->save();
        Device::where('company_id', $company->id)->update([
            'isBlocked' => true,
        ]);
        return response()->json();
    }
    public function unblockCompanies($id)
    {
        $company = Company::where('id', $id)->first();
        $company->isBlocked = false;
        $company->save();
        Device::where('company_id', $company->id)->update([
            'isBlocked' => false,
        ]);
        return response()->json();
    }
    public function blockManager($id)
    {
        $company = User::where('id', $id)->first();
        $company->isBlocked = true;
        $company->save();
        Device::where('users_id', $company->id)->update(['isBlocked' => true]);
        return response()->json();
    }
    public function unblockManager($id)
    {
        $company = User::where('id', $id)->first();
        $company->isBlocked = false;
        $company->save();
        Device::where('users_id', $company->id)->update(['isBlocked' => false]);
        return response()->json();
    }
    public function hideStatistic($id)
    {
        $user = User::where('id', $id)->first();
        $user->hide_statistic = true;
        $user->save();
        return response()->json(['user' => $user]);
    }
    public function unhideStatistic($id)
    {
        $user = User::where('id', $id)->first();
        $user->hide_statistic = false;
        $user->save();
        return response()->json(['user' => $user]);
    }
    public function getStatisticFromOneCompany($company)
    {
        $data = [];
        $data['company'] = $company;
        $data['device'] = Device::where('company_id', $company->id)
            ->with('user')
            ->with('earnings')
            ->with('errors')
            ->with('withdrawals')
            ->get();
        $data['companyTransaction'] = CompanyTransaction::where(
            'company_id',
            $company->id
        )->get();
        $data['totalEarnings'] = 0;
        $data['needToPay'] = 0;
        $data['payedCashback'] = 0;
        $data['payedCompanyFee'] = 0;
        $data['companyFee'] = 0;
        $data['withdrew'] = 0;
        $earnings = [];
        $devicesActivity = [
            'active' => 0,
            'inactive' => 0,
        ];
        $currentTimestamp = time();
        foreach ($data['companyTransaction'] as $key => $trans) {
            if ($trans['type'] == 1) {
                $data['payedCashback'] += $trans['amount'];
            }
            if ($trans['type'] == 2) {
                $data['payedCompanyFee'] += $trans['amount'];
            }
            if ($trans['type'] == 4) {
                $data['withdrew'] += $trans['amount'];
            }
        }
        foreach ($data['device'] as $key => $device) {
            if (!empty($device)) {
                if (count($device['earnings']) !== 0) {
                    $earnings = [...$earnings, ...$device['earnings']];
                    $data['totalEarnings'] += $this->getTotalEarnings(
                        $device['earnings']
                    );

                    $data['needToPay'] +=
                        ($this->getTotalEarnings($device['earnings']) *
                            $device['user']['cashback']) /
                        100;

                    unset($device['earnings']);
                }

                if (strtotime($device['lastBeat']) > $currentTimestamp) {
                    $devicesActivity['active'] += 1;
                } else {
                    $devicesActivity['inactive'] += 1;
                }
                unset($data['device'][$key]);
            }
        }
        $data['companyFee'] +=
            (($data['totalEarnings'] - $data['needToPay']) *
                $data['company']['cashback']) /
            100 /
            100;
        $data['deviceActivity'] = $devicesActivity;
        $data['earnings'] = $this->getEarnings($earnings);
        return $data;
    }

    public function store(Request $request)
    {
        if ($request->cashback < 0 || $request->cashback > 100) {
            return response()->json(
                ['message' => 'არასწორი პროცენტია მითითებული'],
                422
            );
        }
        $user = User::where('email', $request->admin_email)->first();
        if (empty($user)) {
            return response()->json(
                ['message' => 'მომხმარებელი აღნიშნული მაილით ვერ მოიძებნა'],
                422
            );
        }
        $company = Company::create([
            'admin_id' => $user->id,
            ...$request->all(),
        ]);
        return response()->json($company, 201);
    }

    public function payedCashback($company_id, $manager_id)
    {
        $data = [];
        $data['total'] = 0;
        $data['totalWithdrow'] = 0;
        $data['transaction'] = CompanyTransaction::where(
            'company_id',
            $company_id
        )
            ->where('manager_id', $manager_id)
            ->get();
        foreach ($data['transaction'] as $key => $value) {
            if ($value['type'] === 1) {
                $data['total'] += $value->amount;
            }
            if ($value['type'] === 3) {
                $data['totalWithdrow'] += $value->amount;
            }
        }
        return $data;
    }

    public function payCashback(Request $request)
    {
        $valid = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required',
            'manager_id' => 'nullable|integer|exists:users,id', // Assuming manager_id is a foreign key referencing id on users table and it can be nullable
            'transaction_date' => 'required|date',
        ]);
        if ($valid['type'] == 1) {
            $user = User::where('id', $valid['manager_id'])->first();

            $devices = Device::where('company_id', $valid['company_id'])
                ->where('users_id', $valid['manager_id'])
                ->withTrashed()
                ->with('earnings')
                ->get();
            $earnings = [];
            foreach ($devices as $key => $device) {
                if (count($device['earnings']) !== 0) {
                    $earnings = [...$earnings, ...$device['earnings']];
                }
            }
            $total = $this->getTotalEarnings($earnings);
            $payed = CompanyTransaction::where(
                'company_id',
                $valid['company_id']
            )
                ->where('manager_id', $valid['manager_id'])
                ->where('type', 3)
                ->get();
            $totalPayed = 0;
            foreach ($payed as $key => $value) {
                $totalPayed += $value['amount'];
            }
            $maxCashback =
                (($total / 100) * $user['cashback']) / 100 - $totalPayed;
            if ($maxCashback + 1 < $valid['amount']) {
                return response()->json(
                    ['message' => 'მაქსიმალურ ქეშბექზე დიდი თანხაა'],
                    422
                );
            }
        }

        if ($valid['type'] == 2) {
            $data['company'] = Company::where(
                'id',
                $valid['company_id']
            )->first();
            $data['device'] = Device::where('company_id', $valid['company_id'])
                ->with('user')
                ->with('earnings')
                ->get();
            $data['companyTransaction'] = CompanyTransaction::where(
                'company_id',
                $valid['company_id']
            )->get();
            $data['totalEarnings'] = 0;
            $data['needToPay'] = 0;
            $data['payedCashback'] = 0;
            $data['payedCompanyFee'] = 0;
            $earnings = [];
            $devicesActivity = [
                'active' => 0,
                'inactive' => 0,
            ];
            foreach ($data['companyTransaction'] as $key => $trans) {
                if ($trans['type'] == 1) {
                    $data['payedCashback'] += $trans['amount'];
                }
                if ($trans['type'] == 2) {
                    $data['payedCompanyFee'] += $trans['amount'];
                }
            }
            foreach ($data['device'] as $key => $device) {
                if (count($device['earnings']) !== 0) {
                    $data['totalEarnings'] += $this->getTotalEarnings(
                        $device['earnings']
                    );
                    $data['needToPay'] +=
                        ($data['totalEarnings'] * $device['user']['cashback']) /
                        100;
                    unset($device['earnings']);
                }
                unset($data['device'][$key]);
            }
            $data['companyFee'] =
                (($data['totalEarnings'] - $data['needToPay']) *
                    $data['company']['cashback']) /
                100 /
                100;
            if (
                $data['companyFee'] - $data['payedCompanyFee'] <
                $valid['amount']
            ) {
                return response()->json(
                    [
                        'message' =>
                            'მაქსიმალურ სერვისის გადასახადზე დიდი თანხაა',
                    ],
                    422
                );
            }
        }

        if ($valid['type'] == 3) {
            $data = [];
            $data['companyTransaction'] = CompanyTransaction::where(
                'company_id',
                $valid['company_id']
            )->get();
            $data['payedCashback'] = 0;
            $data['withdrCashback'] = 0;

            foreach ($data['companyTransaction'] as $key => $trans) {
                if ($trans['type'] == 1) {
                    $data['payedCashback'] += $trans['amount'];
                }
                if ($trans['type'] == 3) {
                    $data['withdrCashback'] += $trans['amount'];
                }
            }

            if (
                $data['payedCashback'] - $data['withdrCashback'] <
                $valid['amount']
            ) {
                return response()->json(
                    ['message' => 'ამ თანხის გამოტანა შეუძლებელია'],
                    422
                );
            }
        }
        if ($valid['type'] == 4) {
            $data = [];
            $data['companyTransaction'] = CompanyTransaction::where(
                'company_id',
                $valid['company_id']
            )->get();
            $data['payedCompanyFee'] = 0;
            $data['withdrCompanyFee'] = 0;

            foreach ($data['companyTransaction'] as $key => $trans) {
                if ($trans['type'] == 2) {
                    $data['payedCompanyFee'] += $trans['amount'];
                }
                if ($trans['type'] == 4) {
                    $data['withdrCompanyFee'] += $trans['amount'];
                }
            }

            if (
                $data['payedCompanyFee'] - $data['withdrCompanyFee'] <
                $valid['amount']
            ) {
                return response()->json(
                    ['message' => 'ამ თანხის გამოტანა შეუძლებელია'],
                    422
                );
            }
        }

        return CompanyTransaction::create([
            'company_id' => $valid['company_id'],
            'amount' => $valid['amount'],
            'manager_id' => $valid['manager_id'],
            'type' => $valid['type'],
            'transaction_date' => $valid['transaction_date'],
        ]);
    }

    public function deleteCashback($id)
    {
        return CompanyTransaction::where('id', $id)->delete();
    }

    public function show($company_id)
    {
        $data = [];
        $data['company'] = Company::where('id', $company_id)
            ->with('admin')
            ->first();
        $data['device'] = Device::where('company_id', $company_id)
            ->with('user')
            ->with('earnings')
            ->with('errors')
            ->with('withdrawals')

            ->get();

        $data['totalEarnings'] = 0;
        $data['needToPay'] = 0;
        $data['payedCashback'] = 0;
        $data['payedCompanyFee'] = 0;
        $data['withdrowedCompanyFee'] = 0;
        $data['withdrowedCashback'] = 0;
        $earnings = [];
        $managers = [];
        $devicesActivity = [
            'active' => 0,
            'inactive' => 0,
        ];
        $currentTimestamp = time();
        $data['companyTransaction'] = CompanyTransaction::where(
            'company_id',
            $company_id
        )->get();
        foreach ($data['companyTransaction'] as $key => $trans) {
            if ($trans['type'] == 1) {
                $data['payedCashback'] += $trans['amount'];
                continue;
            }
            if ($trans['type'] == 2) {
                $data['payedCompanyFee'] += $trans['amount'];
                continue;
            }
            if ($trans['type'] == 3) {
                $data['withdrowedCashback'] += $trans['amount'];
                continue;
            }
            if ($trans['type'] == 4) {
                $data['withdrowedCompanyFee'] += $trans['amount'];
            }
        }
        $testSom = 0;
        $count = 0;
        foreach ($data['device'] as $key => $device) {
            if (count($device['earnings']) !== 0) {
                $earnings = [...$earnings, ...$device['earnings']];
                $data['totalEarnings'] += $this->getTotalEarnings(
                    $device['earnings']
                );
                $testSom = $this->getTotalEarnings($device['earnings']);
                $count++;
                $data['needToPay'] +=
                    ($this->getTotalEarnings($device['earnings']) *
                        $device['user']['cashback']) /
                    100 /
                    100;
                unset($device['earnings']);
            }
            $managers[$device['user']['id']] = $device['user'];
            // k
            foreach ($managers as $key => $manager) {
                $devices = Device::where('users_id', $manager['id'])
                    ->with('earnings')
                    ->get();
                // foreach ($devices as $key => $deviceId) {
                //     $deviceEarn = DeviceEarn::where(
                //         'device_id',
                //         $deviceId['id']
                //     )->get();
                //     $manager['deviceEarn'] = $deviceEarn;
                // }
                $manager['deviceTariffAmounts'] = $devices
                    ->pluck('deviceTariffAmount')
                    ->toArray();

                $totalTariffAmount = array_sum($manager['deviceTariffAmounts']);
                $manager['totalTariffAmount'] = $totalTariffAmount;

                $managers[$key] = $manager;
            }

            unset($device['user']);
            if (strtotime($device['lastBeat']) > $currentTimestamp) {
                $devicesActivity['active'] += 1;
            } else {
                $devicesActivity['inactive'] += 1;
            }
            unset($data['device'][$key]);
        }
        $data['companyFee'] =
            (($data['totalEarnings'] - $data['needToPay']) *
                $data['company']['cashback']) /
            100 /
            100;

        $data['ssd'] = [
            1 => $data['totalEarnings'],
            2 => $data['needToPay'],
            3 => $data['company']['cashback'],
            4 => $earnings,
        ];

        $data['deviceActivity'] = $devicesActivity;
        $data['earnings'] = $this->getEarnings($earnings);
        $data['managers'] = [...$managers];
        return $data;
    }

    public function manager($id)
    {
        $data = [];
        $data['device'] = Device::where('users_id', $id)
            ->withTrashed()
            ->with('user')
            ->with('earnings')
            ->with('errors')
            ->with('withdrawals')
            ->get();
        $earnings = [];
        $managers = [];
        $devicesActivity = [
            'active' => 0,
            'inactive' => 0,
        ];
        $currentTimestamp = time();

        foreach ($data['device'] as $key => $device) {
            if (count($device['earnings']) !== 0) {
                $earnings = [...$earnings, ...$device['earnings']];
                unset($device['earnings']);
            }
            $managers = $device['user'];
            unset($device['user']);
            if (is_null($device->deleted_at)) {
                if (strtotime($device['lastBeat']) > $currentTimestamp) {
                    $devicesActivity['active'] += 1;
                } else {
                    $devicesActivity['inactive'] += 1;
                }
            }
        }
        $data['deviceActivity'] = $devicesActivity;
        $data['earnings'] = $this->getEarnings($earnings);
        $data['manager'] = $managers;
        return $data;
    }

    public function getTotalEarnings($allEarnings)
    {
        $total = 0;
        foreach ($allEarnings as $earning) {
            $total += floatval($earning['earnings']);
        }
        return $total;
    }

    public function getEarnings($allEarnings)
    {
        $groupedEarnings = [];
        foreach ($allEarnings as $earning) {
            $month = $earning->month;
            $year = $earning->year;
            $key = "$month-$year"; // e.g., "9-2023"
            if (isset($groupedEarnings[$key])) {
                // If the key already exists, sum the earnings
                $groupedEarnings[$key]['earnings'] += floatval(
                    $earning['earnings']
                );
            } else {
                // Or create a new key with initial earning
                $groupedEarnings[$key] = [
                    'month' => $month,
                    'year' => $year,
                    'earnings' => floatval($earning['earnings']),
                    'fullTime' => $earning['updated_at'],
                ];
            }
        }
        return $groupedEarnings;
    }

    public function update(Request $request, Company $company)
    {
        if ($request->cashback < 0 || $request->cashback > 100) {
            return response()->json(
                ['message' => 'არასწორი პროცენტია მითითებული'],
                422
            );
        }
        $user = User::where('email', $request->admin_email)->first();
        if (empty($user)) {
            return response()->json(
                ['message' => 'მომხმარებელი აღნიშნული მაილით ვერ მოიძებნა'],
                422
            );
        }

        $company->update([...$request->all(), 'admin_id' => $user->id]);
        return response()->json($company, 200);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(null, 204);
    }
}
