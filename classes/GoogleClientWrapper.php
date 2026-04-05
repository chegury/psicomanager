<?php
/**
 * GoogleClientWrapper - Integração com Google Calendar e Meet
 * Prioriza o acesso via OAuth2 (Login do Usuário)
 */
class GoogleClientWrapper {
    private $pdo;
    private $clientId;
    private $clientSecret;
    private $calendarId;
    private $accessToken;
    private $isAuthenticated = false;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $this->calendarId = $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary';
        
        $this->loadTokens();
    }

    /**
     * Carrega e renova os tokens do banco se necessário
     */
    private function loadTokens() {
        $stmt = $this->pdo->query("SELECT * FROM google_auth LIMIT 1");
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$auth) return;

        $expiresAt = strtotime($auth['expires_at']);
        
        // Se o token expirou ou vai expirar em 5 min
        if ($expiresAt < (time() + 300)) {
            $this->refreshToken($auth['refresh_token']);
        } else {
            $this->accessToken = $auth['access_token'];
            $this->isAuthenticated = true;
        }
    }

    /**
     * Renova o access_token usando o refresh_token
     */
    private function refreshToken($refreshToken) {
        if (!$refreshToken) return;

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            $expiresAt = date('Y-m-d H:i:s', time() + $data['expires_in']);
            
            $stmt = $this->pdo->prepare("UPDATE google_auth SET access_token = ?, expires_at = ?");
            $stmt->execute([$this->accessToken, $expiresAt]);
            
            $this->isAuthenticated = true;
        } else {
            error_log("GoogleClient: Erro ao renovar token - " . ($data['error_description'] ?? $response));
        }
    }

    /**
     * Cria evento no Google Calendar e gera Meet
     */
    public function createEvent($summary, $description, $startDateTime, $endDateTime, $attendeeEmail = null) {
        if ($this->isAuthenticated && $this->accessToken) {
            return $this->createEventViaApi($summary, $description, $startDateTime, $endDateTime, $attendeeEmail);
        }
        
        // Se não tiver login, volta para o modo manual (Template)
        return $this->createEventManual($summary, $description, $startDateTime, $endDateTime, $attendeeEmail);
    }

    private function createEventViaApi($summary, $description, $startDateTime, $endDateTime, $attendeeEmail) {
        $event = [
            'summary' => $summary,
            'description' => $description,
            'start' => ['dateTime' => $this->formatDateTime($startDateTime), 'timeZone' => 'America/Sao_Paulo'],
            'end' => ['dateTime' => $this->formatDateTime($endDateTime), 'timeZone' => 'America/Sao_Paulo'],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => 'psimgr-' . uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ]
            ]
        ];

        if ($attendeeEmail && filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) {
            $event['attendees'] = [['email' => $attendeeEmail]];
            // Envia e-mail de convite
            $url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($this->calendarId) . "/events?conferenceDataVersion=1&sendUpdates=all";
        } else {
            $url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($this->calendarId) . "/events?conferenceDataVersion=1";
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($event),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        // Se o evento foi criado (tem ID), a sincronização da agenda FUNCIONOU!
        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['id'])) {
            $meetLink = $data['hangoutLink'] ?? null;
            
            // Busca o link da conferência nos entryPoints se necessário
            if (!$meetLink && !empty($data['conferenceData']['entryPoints'])) {
                foreach ($data['conferenceData']['entryPoints'] as $ep) {
                    if ($ep['entryPointType'] === 'video') { $meetLink = $ep['uri']; break; }
                }
            }

            // Se ainda não temos Meet, mas o evento foi criado, usamos o Meet genérico como fallback
            if (!$meetLink) {
                $meetLink = 'https://meet.google.com/new'; // Facilitador
            }

            return [
                'success' => true,
                'id' => $data['id'],
                'link' => $meetLink,
                'htmlLink' => $data['htmlLink'] ?? '#',
                'source' => 'google_api'
            ];
        }

        // Se o Google retornou erro, vamos cair no manual para não travar o sistema
        error_log("Google API CALL FAILED: HTTP $httpCode - Response: " . substr($response, 0, 500));
        return $this->createEventManual($summary, $description, $startDateTime, $endDateTime, $attendeeEmail);
    }

    private function createEventManual($summary, $description, $startDateTime, $endDateTime, $attendeeEmail) {
        $start = $this->toCalendarFormat($startDateTime);
        $end = $this->toCalendarFormat($endDateTime);
        $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($summary) . '&dates=' . $start . '/' . $end . '&details=' . urlencode($description . "\n\nSessão agendada via PsiManager.") . '&location=' . urlencode('Google Meet');
        
        if ($attendeeEmail && filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) $gcalUrl .= '&add=' . urlencode($attendeeEmail);

        return [
            'success' => true,
            'id' => 'gcal_' . uniqid(), 
            'link' => 'https://meet.google.com/new', 
            'htmlLink' => $gcalUrl, 
            'source' => 'google_manual_template'
        ];
    }

    public function getCalendarAddUrl($summary, $description, $startDateTime, $endDateTime, $meetLink = '', $attendeeEmail = '') {
        $start = $this->toCalendarFormat($startDateTime);
        $end = $this->toCalendarFormat($endDateTime);
        $details = $description . ($meetLink ? "\n\nLink da sessão: " . $meetLink : "");
        $url = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode($summary) . "&dates=$start/$end&details=" . urlencode($details) . "&location=" . urlencode($meetLink ?: 'Google Meet');
        if ($attendeeEmail && filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) $url .= '&add=' . urlencode($attendeeEmail);
        return $url;
    }

    public function isAuthenticated() { return $this->isAuthenticated; }
    private function formatDateTime($dt) { return date('Y-m-d\TH:i:s', strtotime($dt)); }
    private function toCalendarFormat($dt) { return date('Ymd\THis', strtotime($dt)); }
}
?>
