<?php
// Funções Auxiliares

/**
 * Formata valor para Real (BRL)
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Formata data para o padrão brasileiro
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata data e hora para o padrão brasileiro
 */
function formatDateTime($date) {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Limpa string para salvar apenas números (CPF, Telefone)
 */
function cleanNumber($string) {
    return preg_replace('/[^0-9]/', '', $string);
}

/**
 * Retorna o nome do dia da semana
 */
function getDayName($dayNumber) {
    $days = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo' // Ajuste conforme necessidade, PHP date('N') 1=Seg, 7=Dom
    ];
    return $days[$dayNumber] ?? 'Desconhecido';
}

/**
 * Envia resposta JSON e encerra a execução
 */
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Redireciona para uma URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}
?>
