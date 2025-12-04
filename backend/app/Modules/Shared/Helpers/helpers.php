<?php

/**
 * Funciones helper compartidas entre módulos
 * Ferretería Frenad
 */

if (!function_exists('format_currency')) {
    /**
     * Formatear cantidad como moneda boliviana
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    function format_currency($amount, $currency = 'Bs'): string
    {
        return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('format_bolivian_date')) {
    /**
     * Formatear fecha con zona horaria de Bolivia
     *
     * @param mixed $date
     * @return string
     */
    function format_bolivian_date($date): string
    {
        return \Carbon\Carbon::parse($date)
            ->timezone('America/La_Paz')
            ->format('d/m/Y H:i:s');
    }
}

if (!function_exists('generate_voucher_number')) {
    /**
     * Generar número de comprobante
     *
     * @param string $prefix
     * @return string
     */
    function generate_voucher_number(string $prefix = 'VTA'): string
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('clean_ruc_nit')) {
    /**
     * Limpiar RUC/NIT (quitar guiones y espacios)
     *
     * @param string $value
     * @return string
     */
    function clean_ruc_nit(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}

if (!function_exists('format_ruc_nit')) {
    /**
     * Formatear RUC/NIT boliviano
     *
     * @param string $value
     * @return string
     */
    function format_ruc_nit(string $value): string
    {
        $cleaned = clean_ruc_nit($value);
        
        if (strlen($cleaned) >= 7) {
            return substr($cleaned, 0, -1) . '-' . substr($cleaned, -1);
        }
        
        return $cleaned;
    }
}
