<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/EnvLoader.php';
require_once __DIR__ . '/classes/GoogleClientWrapper.php';

EnvLoader::load(__DIR__ . '/.env');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'move':
        $id = intval($_POST['id'] ?? 0);
        $newDate = $_POST['new_date'] ?? '';
        
        if (!$id || !$newDate) {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT data_sessao FROM agenda WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            
            if ($current) {
                $time = date('H:i:s', strtotime($current['data_sessao']));
                $newDateTime = $newDate . ' ' . $time;
                
                $stmt = $pdo->prepare("UPDATE agenda SET data_sessao = ? WHERE id = ?");
                $stmt->execute([$newDateTime, $id]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Agendamento não encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM agenda WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'generate_meet':
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }
        
        try {
            // Buscar dados do agendamento e paciente
            $stmt = $pdo->prepare("
                SELECT a.*, p.nome, p.email, p.whatsapp 
                FROM agenda a 
                JOIN pacientes p ON a.paciente_id = p.id 
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$agendamento) {
                echo json_encode(['success' => false, 'error' => 'Agendamento não encontrado']);
                exit;
            }
            
            // Usar o GoogleClientWrapper (tenta API real, cai no Jitsi Meet se não tiver)
            $google = new GoogleClientWrapper();
            
            $inicio = $agendamento['data_sessao'];
            $fim = date('Y-m-d H:i:s', strtotime($inicio . ' +50 minutes'));
            
            $resultado = $google->createEvent(
                "Sessão: " . $agendamento['nome'],
                "Sessão de Psicoterapia com " . $agendamento['nome'],
                $inicio,
                $fim,
                $agendamento['email']
            );
            
            $meetLink = $resultado['link'] ?? null;
            $calendarLink = $resultado['htmlLink'] ?? null;
            $source = $resultado['source'] ?? 'unknown';
            
            if ($meetLink) {
                $statusMsg = "Link gerado!";
                if ($source === 'google_manual_template') {
                    $statusMsg = "Redirecionando para Google Agenda... Salve para criar o Meet.";
                    $stmt = $pdo->prepare("UPDATE agenda SET link_meet = 'Configurando...' WHERE id = ?");
                    $stmt->execute([$id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE agenda SET link_meet = ? WHERE id = ?");
                    $stmt->execute([$meetLink, $id]);
                }
                
                echo json_encode([
                    'success' => true,
                    'meet_link' => $meetLink,
                    'calendar_link' => $calendarLink,
                    'source' => $source,
                    'message' => $statusMsg,
                    'whatsapp' => preg_replace('/\D/', '', $agendamento['whatsapp']),
                    'nome' => $agendamento['nome']
                ]);
            } else {
                $errorMsg = $resultado['error'] ?? 'Não foi possível gerar o link da reunião (Erro desconhecido)';
                echo json_encode(['success' => false, 'error' => $errorMsg]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'set_meet_link':
        $id = intval($_POST['id'] ?? 0);
        $link = $_POST['link'] ?? '';
        
        if (!$id || !$link) {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE agenda SET link_meet = ? WHERE id = ?");
            $stmt->execute([$link, $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'add_to_gcal':
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, p.nome, p.email 
                FROM agenda a 
                JOIN pacientes p ON a.paciente_id = p.id 
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $ag = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ag) {
                echo json_encode(['success' => false, 'error' => 'Agendamento não encontrado']);
                exit;
            }
            
            $google = new GoogleClientWrapper();
            $inicio = $ag['data_sessao'];
            $fim = date('Y-m-d H:i:s', strtotime($inicio . ' +50 minutes'));
            
            $calUrl = $google->getCalendarAddUrl(
                "Sessão: " . $ag['nome'],
                "Sessão de Psicoterapia",
                $inicio,
                $fim,
                $ag['link_meet'] ?? '',
                $ag['email'] ?? ''
            );
            
            echo json_encode(['success' => true, 'calendar_url' => $calUrl]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Ação desconhecida']);
}
?>
