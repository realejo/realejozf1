<?php
namespace RealejoZf1\Utils;

class Debug
{
    public static function sendFirebug($message, $priority = \Zend_Log::INFO, $extras = null)
    {
        // Verifica se está em produção
        if (APPLICATION_ENV === 'production') {
            return;
        }

        // Recupera o logger do firebug
        if (\Zend_Registry::isRegistered('firebug')) {
            $firebug = \Zend_Registry::get('firebug');
        } else {
            $firebug = new \Zend_Log();
            $firebug->addWriter(new \Zend_Log_Writer_Firebug());
            \Zend_Registry::set('firebug', $firebug);
        }

        $firebug->log($message, $priority, $extras);
    }
}
