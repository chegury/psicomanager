<?php

class AsaasClient {
    private $apiKey;
    private $apiUrl;
    public $lastError = null;
    public $lastResponse = null;

    public function __construct() {
        $this->apiKey = trim($_ENV['ASAAS_API_KEY'] ?? getenv('ASAAS_API_KEY') ?? '');
        $this->apiUrl = $_ENV['ASAAS_URL'] ?? getenv('ASAAS_URL') ?? '';
        
        if (!$this->apiUrl) {
            // Detecção automática baseada na chave
            // Chaves de produção começam com $aact_prod_
            if (strpos($this->apiKey, '$aact_prod_') === 0) {
                $this->apiUrl = 'https://www.asaas.com/api/v3';
            } else {
                $this->apiUrl = 'https://sandbox.asaas.com/api/v3';
            }
        }
        
        $this->apiUrl = rtrim(trim($this->apiUrl), '/');
    }

    /**
     * Cria uma nova cobrança no Asaas
     */
    public function createPayment($customerData, $billingData) {
        $endpoint = '/payments';
        
        // Dados mínimos para criar cobrança
        $payload = [
            'customer' => $customerData['id'], // ID do cliente no Asaas
            'billingType' => 'BOLETO', // Ou PIX, CREDIT_CARD
            'value' => floatval($billingData['value']),
            'dueDate' => $billingData['dueDate'],
            'description' => $billingData['description'] ?? 'Cobrança PsiManager',
        ];
        
        if (!empty($billingData['externalReference'])) {
            $payload['externalReference'] = $billingData['externalReference'];
        }
        
        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Cria ou recupera um cliente no Asaas
     */
    public function createCustomer($name, $cpf, $email = null, $phone = null) {
        $endpoint = '/customers';
        
        // Limpa o CPF (apenas números)
        $cpf = preg_replace('/\D/', '', $cpf);
        
        // Primeiro, verificar se já existe (busca por CPF)
        try {
            $existing = $this->request('GET', $endpoint . "?cpfCnpj=" . $cpf);
            if (!empty($existing['data'])) {
                return $existing['data'][0]['id'];
            }
        } catch (Exception $e) {
            // Se erro ao buscar, tenta criar mesmo assim
        }

        $payload = [
            'name' => $name,
            'cpfCnpj' => $cpf,
        ];
        
        // Adiciona email apenas se válido
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $payload['email'] = $email;
        }
        
        // Adiciona telefone apenas se fornecido
        if ($phone) {
            $payload['mobilePhone'] = preg_replace('/\D/', '', $phone);
        }

        $response = $this->request('POST', $endpoint, $payload);
        return $response['id'] ?? null;
    }
    
    /**
     * Consulta uma cobrança específica pelo ID
     */
    public function getPayment($asaasId) {
        $endpoint = '/payments/' . $asaasId;
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Lista todas cobranças de um cliente
     */
    public function listPayments($customerId = null, $status = null) {
        $endpoint = '/payments';
        $params = [];
        
        if ($customerId) {
            $params['customer'] = $customerId;
        }
        if ($status) {
            $params['status'] = $status;
        }
        
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        
        return $this->request('GET', $endpoint);
    }

    /**
     * Lista todos os clientes do Asaas (paginado)
     */
    public function listCustomers($offset = 0, $limit = 100) {
        $endpoint = '/customers?offset=' . $offset . '&limit=' . $limit;
        return $this->request('GET', $endpoint);
    }

    public function request($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
            'User-Agent: PsiManager/1.0'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para ambientes locais
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                $jsonData = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Armazena para debug
        $this->lastResponse = $response;
        
        if ($curlError) {
            $this->lastError = "Erro de conexão: $curlError";
            throw new Exception($this->lastError);
        }

        $decoded = json_decode($response, true);
        
        // Log para debug
        error_log("Asaas Request: $method $url");
        error_log("Asaas Response ($httpCode): " . substr($response, 0, 500));

        if ($httpCode >= 400) {
            // Captura todos os erros possíveis
            $errorMessages = [];
            
            if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
                foreach ($decoded['errors'] as $err) {
                    $errorMessages[] = $err['description'] ?? $err['code'] ?? json_encode($err);
                }
            }
            
            if (empty($errorMessages) && !empty($decoded['message'])) {
                $errorMessages[] = $decoded['message'];
            }
            
            if (empty($errorMessages)) {
                $errorMessages[] = "HTTP $httpCode - Resposta: " . substr($response, 0, 200);
            }
            
            $this->lastError = implode(' | ', $errorMessages);
            error_log("Asaas API Error: " . $this->lastError);
            throw new Exception($this->lastError);
        }

        return $decoded;
    }
    
    /**
     * Retorna o último erro para debug
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Retorna a última resposta raw para debug
     */
    public function getLastResponse() {
        return $this->lastResponse;
    }
}
