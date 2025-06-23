<?php
/**
 * Plugin Name: Botones por Grupo Docente DEPEZ
 * Description: Muestra los grupos y semestres que imparte el docente, redirige con parámetros correctos.
 * Version: 1.6.2
 * Author: Juan Taniz
 */

// Seguridad: Salir si se accede directamente
if (!defined('ABSPATH')) exit;

// Iniciar sesión si no está activa
if (!session_id()) session_start();

// Registrar shortcode
add_shortcode('botones_grupos_docente', 'mostrar_botones_grupos_docente');

function mostrar_botones_grupos_docente() {
    global $wpdb;

    // Verificar sesión de usuario
    if (!isset($_SESSION['usuario_id'])) {
        return '<p>Sesión no iniciada. Debes iniciar sesión como docente.</p>';
    }

    // Obtener ID de usuario y verificar rol docente
    $id_usuario = intval($_SESSION['usuario_id']);
    $roles = $wpdb->get_col($wpdb->prepare(
        "SELECT id_rol FROM usuariorol WHERE id_usuario = %d",
        $id_usuario
    ));

    if (!in_array(1, $roles)) {
        return '<p>Acceso restringido. Solo para docentes.</p>';
    }

    // Obtener grupos asignados al docente
    $resultados = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT id_grupo AS grupo_id, semestre 
         FROM asignacionusuariomateria 
         WHERE id_usuario = %d 
         ORDER BY semestre, grupo_id",
        $id_usuario
    ));

    if (!$resultados) {
        return '<p>No se encontraron grupos asignados para este docente.</p>';
    }

    // Organizar grupos por semestre
    $agrupado = [];
    foreach ($resultados as $fila) {
        $nombre_grupo = $wpdb->get_var($wpdb->prepare(
            "SELECT nombre FROM grupo WHERE id_grupo = %d",
            $fila->grupo_id
        ));
        $agrupado[$fila->semestre][] = $nombre_grupo;
    }

    // Construir HTML
    $html = '<div class="contenedor-semestres">';
    
    foreach ($agrupado as $semestre => $grupos) {
        $html .= "<div class='bloque-semestre'>";
        $html .= "<h3 class='titulo-semestre'>Semestre $semestre</h3>";
        $html .= "<div class='grupos-botones'>";
        
        foreach ($grupos as $grupo) {
            $url = site_url("/?page_id=1063&grupo=$grupo&semestre=$semestre");
            $html .= "<a href='$url' class='boton-grupo'>$grupo</a>";
        }
        
        $html .= "</div></div>";
    }

    // Botón de regreso
    $html .= '<div style="text-align:center;margin-top:30px;">
        <a href="javascript:history.back()" class="boton-regresar">Regresar</a>
    </div>';

    // Estilos CSS
    $html .= "<style>
        .contenedor-semestres { max-width: 600px; margin: auto; font-family: Arial; }
        .bloque-semestre { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .titulo-semestre { text-align: center; font-size: 20px; margin-bottom: 10px; }
        .grupos-botones { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
        .boton-grupo { 
            background-color: #555; color: white; padding: 10px 18px; 
            text-decoration: none; border-radius: 8px; font-family: Arial; 
            font-weight: bold; transition: background-color 0.3s; 
            min-width: 48px; text-align: center; 
        }
        .boton-grupo:hover { background-color: #333; }
        .boton-regresar { 
            display: inline-block; padding: 10px 20px; 
            background-color: #e60000; color: white; 
            font-family: Arial, sans-serif; text-decoration: none; 
            border-radius: 6px; font-weight: bold; 
        }
    </style>";

    return $html;
}