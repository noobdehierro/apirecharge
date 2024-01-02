<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Order;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

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
        // return $responseObject;

        if (isset($responseObject->status) && $responseObject->status != 'success') {
            return response()->json([
                'status' => 'fail',
                'offerings' => $offerings
            ]);
        }
        $response_offerings = $responseObject->data->offerings;
        $offerings = [];
        $email = $responseObject->data->email;

        if ($response_offerings) {
            foreach ($response_offerings as $offering) {

                array_push($offerings, $this->fill_offering_data($offering));
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'offerings' => $offerings,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'offerings' => $offerings,
            'email' => $email
        ], 200);
    }

    public function saveOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offering_name' => 'required',
            'offering_price' => 'required',
            'offering_id' => 'required',
            'msisdn' => 'required',
            'email' => 'required'
        ]);

        if ($validator->fails()) {

            return response()->json(
                [
                    "status" => "fail",
                    "data" => $validator->errors()
                ],
                400
            );
        }

        $response = json_decode($this->conekta_generated_pay($request->offering_name, $request->offering_price, $request->offering_id, $request->msisdn, $request->email));

        $me_reference_id = "PWAR" . substr(uniqid(), -10);

        $barcode_url = $response->charges->data[0]->payment_method->barcode_url;
        $reference = $response->charges->data[0]->payment_method->reference;
        $payment_request_id = $response->charges->data[0]->id;
        $order_id = $response->charges->data[0]->order_id;
        $store_name = $response->charges->data[0]->payment_method->store_name;

        $order = Order::create([
            'msisdn' => $request->msisdn,
            'email' => $request->email,
            'offering_name' => $request->offering_name,
            'amount' => $request->offering_price,
            'offering_id' => $request->offering_id,
            'me_reference_id' => $me_reference_id,
            'payment_request_id' => $payment_request_id,
            'payment_id' => $order_id,
            'status' => 'pending',
            'sales_type' => 'Recarga',
            'payment_method' => 'Conekta cash',
            'reference_id' => $reference
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [$order, $barcode_url, $reference, $order_id, $store_name]
        ], 200);
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

    public function conekta_generated_pay($offering_name, $offering_price, $offering_id, $msisdn, $email)
    {

        $configuration = Configuration::wherein('code', [
            'is_sandbox',
            'conekta_private_api_key_sandbox',
            'conekta_private_api_key'
        ])->get();

        foreach ($configuration as $config) {
            if ($config->code == 'is_sandbox') {
                $is_sandbox = $config->value;
            }
            if ($config->code == 'conekta_private_api_key_sandbox') {
                $conekta_private_api_key_sandbox = $config->value;
            }
            if ($config->code == 'conekta_private_api_key') {
                $conekta_private_api_key = $config->value;
            }
        }

        if ($is_sandbox === 'true') {
            $conekta_private_api_key = $conekta_private_api_key_sandbox;
        } else {
            $conekta_private_api_key = $conekta_private_api_key;
        }

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($conekta_private_api_key . ':'),
            'accept' => 'application/vnd.conekta-v2.0.0+json',
        ];
        $thirty_days_from_now = (new DateTime())->add(new DateInterval('P30D'))->getTimestamp();

        $body = [
            "line_items" => [
                [
                    "name" => $offering_name,
                    "unit_price" => $offering_price * 100,
                    "quantity" => 1
                ]

            ],
            "currency" => "MXN",
            "customer_info" => [
                "name" => 'PWARecarga',
                "email" => $email,
                "phone" => +52 . $msisdn
            ],
            "metadata" => [
                "productId" => $offering_id,
                'tipo' => 'PWARecarga',
            ],
            "charges" => [
                [
                    "payment_method" => [
                        "type" => "cash",
                        "expires_at" => $thirty_days_from_now
                    ]
                ]
            ]

        ];


        $response = Http::withHeaders($headers)->post('https://api.conekta.io/orders', $body);


        return $response;
    }
}
