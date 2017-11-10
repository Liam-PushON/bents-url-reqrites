<?php

namespace App;

class Magneto2Client
{

    /**
     * @var \GuzzleHttp\Client
     */
    public $client;

    /**
     * @var string
     */
    public $token;

    public function __construct($base_url)
    {
        $this->client = new \GuzzleHttp\Client(["base_uri" => $base_url]);
    }

    public function generateToken($username, $password)
    {
        $res = $this->client->post(
            "rest/all/V1/integration/admin/token", [
                "Content-Type" => "application/json",
                "json"         => ["username" => $username, "password" => $password]
            ]
        );
        $this->token = trim($res->getBody(), "\"");
    }

    public function getProduct($sku)
    {
        $res = $this->client->get("rest/all/V1/products/{$sku}", [
                "Content-Type" => "application/json",
                "headers"      => [
                    "Authorization" => "Bearer {$this->token}"
                ]
            ]
        );
        return json_decode(trim($res->getBody(), "\""));
    }

    public function updateUrlKey($product, $url_key)
    {
        foreach ($product->custom_attributes as $key => $attribute) {
            if ($attribute->attribute_code == "url_key") {
                $current_url = $product->custom_attributes[$key]->value;
                if (strpos($current_url, $product->sku) == false) {
                    if (!$url_key) {
                        $product->custom_attributes[$key]->value = "{$current_url}-{$product->sku}";
                    } else {
                        $product->custom_attributes[$key]->value = $url_key;
                    }
                }
                file_put_contents(
                    "output/rewrites.csv",
                    "rewrite ^/{$current_url}/$ /{$product->custom_attributes[$key]->value} permanent;\n",
                    8
                );
                return $this->saveProduct($product);
            }
        }
    }

    private function saveProduct($product)
    {
        $res = $this->client->put("rest/all/V1/products/{$product->sku}", [
            "Content-Type" => "application/json",
            "headers"      => [
                "Authorization" => "Bearer {$this->token}"
            ],
            "json"         => ["product" => $product]
        ]);
        return $res;
    }
}