<?php

class WhatsAppClient {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        $this->apiUrl = $_ENV['WHATSAPP_API_URL'] ?? getenv('WHATSAPP_API_URL');
        $this->apiKey = $_ENV['WHATSAPP_API_KEY'] ?? getenv('WHATSAPP_API_KEY');
    }

    /**
     * Envia mensagem de texto via WhatsApp
     */
    public function sendMessage($phone, $message) {
        // Remove formatação do telefone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Adiciona código do país se não tiver (assumindo BR 55)
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        // Exemplo de payload genérico (depende do gateway usado)
        $payload = [
            'number' => $phone,
            'message' => $message,
            // Alguns gateways usam 'text', 'body', etc. Ajustar conforme documentação real.
        ];

        // Se a URL não estiver configurada, apenas loga (modo simulação)
        if (empty($this->apiUrl)) {
            error_log("[WhatsApp Mock] Para: $phone | Msg: $message");
            return true;
        }

        return $this->request('POST', '', $payload);
    }

    private function request($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey // Exemplo de auth
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            error_log("WhatsApp API Error [$httpCode]: $response");
            return false;
        }

        return json_decode($response, true);
    }
}
