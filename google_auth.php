<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

// 1. Se receber o código do Google (Callback)
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Troca o código pelo token de acesso
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $code,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    
    if (isset($data['access_token'])) {
        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'] ?? null; // Só recebemos na primeira vez
        $expiresIn = $data['expires_in'];
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        
        // Salva ou atualiza no banco
        $pdo->exec("DELETE FROM google_auth"); // Limpa anteriores por segurança
        $stmt = $pdo->prepare("INSERT INTO google_auth (access_token, refresh_token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$accessToken, $refreshToken, $expiresAt]);
        
        header('Location: google_auth.php?success=1');
        exit;
    } else {
        $error = "Erro na troca do token: " . ($data['error_description'] ?? $response);
    }
}

// 2. Se o usuário clicar em Conectar
if (isset($_GET['login'])) {
    $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar.readonly',
        'access_type' => 'offline', // Importante para receber o refresh_token
        'prompt' => 'consent' // Garante que o refresh_token venha na primeira vez
    ]);
    header("Location: $authUrl");
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-xl mx-auto mt-12">
    <div class="card-shadow p-8 text-center animate-fade-in">
        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fab fa-google text-3xl text-blue-600"></i>
        </div>
        
        <h2 class="text-2xl font-black text-gray-800 mb-2">Google Agenda + Meet</h2>
        <p class="text-gray-500 mb-8 px-4">Conecte sua conta para gerar links de teleconsulta e sincronizar sua agenda automaticamente com o Psicomanager.</p>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-xl flex flex-col items-center gap-3">
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
                <div class="text-center">
                    <p class="font-bold text-green-800">Conectado com sucesso!</p>
                    <p class="text-xs text-green-600">Sua agenda agora está sincronizada.</p>
                </div>
            </div>
            <a href="agenda.php" class="btn btn-primary w-full py-4 text-base"><i class="fas fa-calendar-alt mr-2"></i> Ir para Agenda</a>
        <?php elseif (isset($error)): ?>
            <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-medium">
                <i class="fas fa-exclamation-triangle mr-1"></i> <?php echo $error; ?>
            </div>
            <a href="google_auth.php?login=1" class="btn btn-primary w-full py-4 text-base shadow-xl shadow-blue-200">
                <i class="fab fa-google mr-2"></i> Tentar novamente
            </a>
        <?php else: ?>
            <div class="space-y-4">
                <a href="google_auth.php?login=1" class="btn btn-primary w-full py-4 text-base shadow-xl shadow-blue-200 flex items-center justify-center gap-3">
                    <i class="fab fa-google text-xl"></i> 
                    <span>Conectar com Google</span>
                </a>
                <p class="text-[10px] text-gray-400">Ao conectar, o Psicomanager poderá visualizar e editar eventos no seu calendário principal.</p>
            </div>
        <?php endif; ?>

        <div class="mt-8 pt-8 border-t border-gray-100 flex items-center justify-center gap-6">
            <div class="text-center">
                <i class="fas fa-shield-alt text-gray-300 mb-1"></i>
                <p class="text-[10px] font-bold text-gray-400">SEGURO</p>
            </div>
            <div class="text-center">
                <i class="fas fa-lock text-gray-300 mb-1"></i>
                <p class="text-[10px] font-bold text-gray-400">CRIPTOGRAFADO</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
