<?php

require_once '../../config/config.php';

class AssinaturaController
{
    public static function createSubscription($data)
    {
        $jsonData = json_encode($data);

        $curlSubscription = curl_init();

        curl_setopt_array($curlSubscription, [
            CURLOPT_URL => ASAAS_API_URL . "/subscriptions/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "User-Agent: " . ASAAS_USER_AGENT,
                "access_token: " . ASAAS_ACCESS_TOKEN,
            ],
        ]);

        $subscriptionResponse = curl_exec($curlSubscription);
        $subscriptionErr = curl_error($curlSubscription);

        curl_close($curlSubscription);

        if ($subscriptionErr) {
            throw new Exception("Erro na criação da assinatura: " . $subscriptionErr);
        }

        $subscriptionResult = json_decode($subscriptionResponse, true);
        if ($subscriptionResult && isset($subscriptionResult['id'])) {
            return $subscriptionResult;
        } else {
            throw new Exception("Erro na resposta da criação da assinatura: " . json_encode($subscriptionResult));
        }
    }
}
?>