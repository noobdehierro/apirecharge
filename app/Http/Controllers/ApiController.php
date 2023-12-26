<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function getOffers(Request $request)
    {

        $configuration = $this->getConfiguration();

        $endpoint = $configuration['endpoint'] . '/recharge';
        $token = $configuration['token'];

        $offerings = collect([]);

        $body = [
            'msisdn' => $request->msisdn
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($endpoint, $body);

        $responseObject = json_decode($response);

        if (isset($responseObject->status) != 'success') {
            return response()->json([
                'status' => 'fail',
                'offerings' => $offerings
            ]);
        }
        $response_offerings = $responseObject->data->offerings;
        $offerings = [];

        if ($response_offerings) {
            foreach ($response_offerings as $offering) {

                array_push($offerings, $this->fill_offering_data($offering));
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'offerings' => $offerings
            ]);
        }

        return response()->json([
            'status' => 'success',
            'offerings' => $offerings
        ], 200);
    }

    public function saveOrder(Request $request)
    {

        $request->validate([
            'msisdn' => 'required',
            'offering_id' => 'required',
            'offering_name' => 'required',
            'amount' => 'required',
        ]);

        $me_reference_id = "PWAR" . substr(uniqid(), -10);

        $request['status'] = 'pending';
        $request['me_reference_id'] = $me_reference_id;
        $request['sales_type'] = 'Recarga';

        $order = Order::create($request->all());

        return response()->json($order);
    }

    public function postOffer(Request $request)
    {

        $configuration = $this->getConfiguration();

        $endpoint = $configuration['endpoint'] . '/recharge';
        $token = $configuration['token'];

        // $msisdn = $request->msisdn;

        $body = [
            "msisdn" => $request->msisdn,
            "offering_id" => $request->offering_id,
            "amount" => $request->amount,
            "payment_method" => $request->payment_method ?? 'CARD',
            "payment_method_name" => $request->payment_method_name ?? 'CARD',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post($endpoint, $body);
        $responseObject = json_decode($response);

        return response()->json($responseObject);
    }

    private function getConfiguration()
    {
        $configuration = Configuration::wherein('code', [
            'is_sandbox',
            'token_sandbox',
            'token',
            'bip_endpoint_sandbox',
            'bip_endpoint'
        ])->get();

        foreach ($configuration as $config) {
            if ($config->code == 'is_sandbox') {
                $is_sandbox = $config->value;
            }
            if ($config->code == 'token_sandbox') {
                $token_sandbox = $config->value;
            }
            if ($config->code == 'token') {
                $token = $config->value;
            }
            if ($config->code == 'bip_endpoint_sandbox') {
                $bip_endpoint_sandbox = $config->value;
            }
            if ($config->code == 'bip_endpoint') {
                $bip_endpoint = $config->value;
            }
        }

        if ($is_sandbox === 'true') {
            $token = $token_sandbox;
            $bip_endpoint = $bip_endpoint_sandbox;
        } else {
            $token = $token;
            $bip_endpoint = $bip_endpoint;
        }

        $response = Http::get($bip_endpoint . "/token", [
            "client_token" => $token
        ]);

        $responseObject = json_decode($response);

        return ([
            'endpoint' => $bip_endpoint,
            'token' => $responseObject->data->access_token
        ]);
    }

    private function fill_offering_data($offering)
    {

        $productId = $offering->supplementary_id;
        $specialPrice = $offering->price ?? $offering->total_price  . ' ' . 'MXN';
        $price = $specialPrice;
        $superOferta = '';
        $origDescription = $offering->description;
        $descLines = preg_split("/\r\n|\r|\n/", $origDescription);
        $description = '<ul>';
        $internalName = $offering->name ?? '';

        foreach ($descLines as $descLine) {
            $description .= '<li><span class="igou-list-icon"><i aria-hidden="true" class="fas fa-check-circle"></i></span><span class="igou-list-text">' . $descLine . '</span></li>';
        }

        $description .= '</ul>';

        return [
            'productId' => $productId,
            'superOferta' => $superOferta,
            'name' => $offering->display_name ?? $offering->offering_name,
            'price' => $price,
            'specialPrice' => $specialPrice,
            'description' => $description,
            'internalName' => $internalName
        ];
    }
}
