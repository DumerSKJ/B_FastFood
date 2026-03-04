<?php

namespace App\Controllers\Shared;

class CacheController
{
    public function getScripts()
    {
        $scripts = [
            // Núcleo y Utilidades
            "public/js/AuthEndPoint.js",

            // Layout y Estructura Base
            "public/js/Layout/sidebar.js",

            // Especialización por Módulos
            "public/js/Dashboard/endpoint_dashboard.js",
            "public/js/Dashboard/logic_dashboard.js",
            "public/js/Dashboard/manager_dashboard.js",

            "public/js/Configuracion/Usuarios/endpoint_usuarios.js",
            "public/js/Configuracion/Usuarios/logic_usuarios.js",
            "public/js/Configuracion/Usuarios/manager_usuarios.js",

            "public/js/Configuracion/Personal/endpoint_personal.js",
            "public/js/Configuracion/Personal/logic_personal.js",
            "public/js/Configuracion/Personal/manager_personal.js",

            "public/js/Configuracion/Rol/endpoint_rol.js",
            "public/js/Configuracion/Rol/logic_rol.js",
            "public/js/Configuracion/Rol/manager_rol.js",

            "public/js/Configuracion/Menu/endpoint_menu.js",
            "public/js/Configuracion/Menu/logic_menu.js",
            "public/js/Configuracion/Menu/manager_menu.js",

            "public/js/PuntoVenta/VentaTablet/endpoint_ventaTablet.js",
            "public/js/PuntoVenta/VentaTablet/logic_ventaTablet.js",
            "public/js/PuntoVenta/VentaTablet/manager_ventaTablet.js",

            "public/js/PuntoVenta/VentaMovil/endpoint_ventaMovil.js",
            "public/js/PuntoVenta/VentaMovil/logic_ventaMovil.js",
            "public/js/PuntoVenta/VentaMovil/manager_ventaMovil.js",

            "public/js/PuntoVenta/VentaMonitor/endpoint_ventaMonitor.js",
            "public/js/PuntoVenta/VentaMonitor/logic_ventaMonitor.js",
            "public/js/PuntoVenta/VentaMonitor/manager_ventaMonitor.js",
            // Coordinador y Enrutador Final
            "public/js/Inicio.js"
        ];

        $scriptsWithVersion = [];

        foreach ($scripts as $src) {
            // Normalizar rutas para Windows/Linux
            $srcPath = str_replace('/', DIRECTORY_SEPARATOR, $src);
            $fullPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'FRONTEND' . DIRECTORY_SEPARATOR . $srcPath;

            $version = file_exists($fullPath) ? filemtime($fullPath) : '1.0.0';

            $scriptsWithVersion[] = [
                'src' => $src,
                'v' => $version
            ];
        }

        return [
            'success' => true,
            'scripts' => $scriptsWithVersion,
            'update_check_clicks' => \App\DatabaseConfig::getUpdateCheckClicks()
        ];
    }
}
