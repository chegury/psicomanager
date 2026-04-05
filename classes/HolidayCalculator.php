<?php

class HolidayCalculator {
    
    /**
     * Verifica se uma data é feriado nacional
     * @param string $date Data no formato Y-m-d
     * @return boolean
     */
    public static function isHoliday($date) {
        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);
        
        // Feriados Fixos
        $fixedHolidays = [
            '01-01', // Confraternização Universal
            '04-21', // Tiradentes
            '05-01', // Dia do Trabalho
            '09-07', // Independência do Brasil
            '10-12', // Nossa Senhora Aparecida
            '11-02', // Finados
            '11-15', // Proclamação da República
            '11-20', // Dia da Consciência Negra (Nacional a partir de 2024)
            '12-25', // Natal
        ];
        
        if (in_array("$month-$day", $fixedHolidays)) {
            return true;
        }
        
        // Feriados Móveis (Baseados na Páscoa)
        $easter = easter_date($year); // Retorna timestamp da Páscoa
        
        // Carnaval (47 dias antes da Páscoa)
        $carnival = date('Y-m-d', strtotime('-47 days', $easter));
        
        // Sexta-feira Santa (2 dias antes da Páscoa)
        $goodFriday = date('Y-m-d', strtotime('-2 days', $easter));
        
        // Corpus Christi (60 dias após a Páscoa)
        $corpusChristi = date('Y-m-d', strtotime('+60 days', $easter));
        
        $movableHolidays = [$carnival, $goodFriday, $corpusChristi];
        
        if (in_array($date, $movableHolidays)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Retorna o nome do feriado (opcional, para debug/display)
     */
    public static function getHolidayName($date) {
        if (!self::isHoliday($date)) return null;
        
        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);
        
        $map = [
            '01-01' => 'Confraternização Universal',
            '04-21' => 'Tiradentes',
            '05-01' => 'Dia do Trabalho',
            '09-07' => 'Independência do Brasil',
            '10-12' => 'Nossa Senhora Aparecida',
            '11-02' => 'Finados',
            '11-15' => 'Proclamação da República',
            '11-20' => 'Dia da Consciência Negra',
            '12-25' => 'Natal',
        ];
        
        if (isset($map["$month-$day"])) return $map["$month-$day"];
        
        $easter = easter_date($year);
        if ($date == date('Y-m-d', strtotime('-47 days', $easter))) return 'Carnaval';
        if ($date == date('Y-m-d', strtotime('-2 days', $easter))) return 'Sexta-feira Santa';
        if ($date == date('Y-m-d', strtotime('+60 days', $easter))) return 'Corpus Christi';
        
        return 'Feriado';
    }
}
?>
