<?php

namespace App\Actions\User\Wallet;

use App\Models\Wallet;
use App\Traits\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Action;

class CreditWallet extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "amount" => "required|numeric",
            "trans_ref" => "required|string|unique:transactions,transaction_reference"
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $transaction = false;
        if ($this->verifyCreditTransaction()) {

            $wallet = $this->user()->wallet;

            try {
                if ($wallet) {
                    $wallet->current_balance += $this->amount;
                    $wallet->save();
                } else {
                    $wallet = Wallet::create([
                        'user_id' => $this->user()->id,
                        'current_balance' => $this->amount
                    ]);
                }

                $transaction = $wallet->transaction()->create([
                    'value' => $this->amount,
                    'description' => "wallet credit",
                    'status' => 1,
                    'transaction_reference' => $this->trans_ref,
                ]);
            } catch (QueryException $queryException) {
                throw new \Exception($queryException->getMessage(), 400);
            } catch (\Throwable $throwable) {
                throw new \Exception($throwable->getMessage());
            }
        }

        return $transaction;
    }

    public function response($result)
    {
        if ($result) {
            return JsonResponse::success($result, "Wallet Credit Successful");
        } else {
            return JsonResponse::error([], "Couldn't verify payment.");
        }
    }

    protected function verifyCreditTransaction(): bool
    {
        $url = "https://api.paystack.co/transaction/verify/{$this->trans_ref}";
        $paymentGatewaySecret = config('services.paystack.secret');

        $response = Http::withHeaders(['Authorization' => "Bearer $paymentGatewaySecret"])
            ->get($url)
            ->json();

        if ($response['status'] && !empty($response['data'] && $response['data']['amount'] === $this->amount*100)) {
            $this->saveDataDump($response);

            return true;
        }

        return false;
    }

    protected function saveDataDump(array $response): void
    {
        $dataDump = [
            'source' => 'paystack',
            'data' => json_encode($response),
            'created_at' =>now(),
            'updated_at' =>now(),
        ];


        $dumpExists = DB::table('data_dump')->where('data', json_encode($response))->exists();

        if (!$dumpExists) {
            DB::table('data_dump')->insert($dataDump);
        }
    }
}
