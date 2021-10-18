<?php

namespace App\Actions\Binance;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class GetAssets24hTicker extends Action
{
    private array $currentAssetData = [];
    private $baseSymbol = "USDT";

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
            'user_id' => 'required|integer'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $assets = Asset::whereHas('logs', function ($query) {
            $query->where('user_id', $this->user_id);
        })->get();

        foreach ($assets as $asset) {
            $assetSymbol = $asset->symbol;
            $activeApi = $asset->assetType->activeApi;

            $pair = "{$assetSymbol}{$this->baseSymbol}";
            $url = $activeApi->host . "/ticker/24hr?symbol={$pair}";

            try {
                $data = Http::retry(3)->get($url);

                $assetData = $data->json();

                Log::info("Fetching update for {$pair}", [$assetData]);

                $this->currentAssetData[$assetSymbol] = array_merge($assetData, ["symbol" => $assetSymbol]);
            } catch (RequestException $requestException) {
                Log::info('message', [$requestException->getMessage()]);
                Log::info('code', [$requestException->response['code']]);
                Log::info('content', [$requestException->response['msg']]);
                Log::info('pair', [$pair]);
                continue;
            } catch (\Throwable $throwable) {
                throw \Exception($throwable->getMessage());
            }

        }

        return $this->currentAssetData;
    }
}
