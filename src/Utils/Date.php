<?php
namespace RealejoZf1\Utils;

class Date
{
    /**
     *
     * Retorna se uma data é valida
     *
     * @param string $date
     * @param string $format
     *
     * @return true
     */
    public static function isFormat($format, $date)
    {
        $date = \DateTime::createFromFormat($format, $date);
        $date_errors = \DateTime::getLastErrors();
        return (($date_errors['warning_count'] + $date_errors['error_count']) == 0);
    }
}
