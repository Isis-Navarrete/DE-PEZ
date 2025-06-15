<?php
/**
 * Plugin Name: Botones por Grupo Docente DEPEZ
 * Description: Muestra los grupos y semestres que imparte el docente, redirige con parámetros correctos.
 * Version: 1.5
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;
if (!session_id()) session_start();

add_shortcode('botones_grupos_docente', 'mostrar_botones_grupos_docente');

function mostrar_botones_grupos_docente() {
    global $wpdb;

    if (!isset($_SESSION['usuario_id'])) {
        return '<p>Sesión no iniciada. Debes iniciar sesión como docente.</p>';
    }

    $id_usuario = intval($_SESSION['usuario_id']);

    // Obtener roles
    $roles = $wpdb->get_col($wpdb->prepare(
        "SELECT id_rol FROM usuariorol WHERE id_usuario = %d", $id_usuario
    ));

    if (!in_array(1, $roles)) {
        return '<p>Acceso restringido. Solo para docentes.</p>';
    }

    // Obtener grupos y semestre donde imparte clases
    $resultados = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT id_grupo AS grupo_id, semestre
         FROM asignacionusuariomateria
         WHERE id_usuario = %d", $id_usuario
    ));

    if (!$resultados) {
        return '<p>No se encontraron grupos asignados para este docente.</p>';
    }

    // Depuración opcional (puedes comentar esto si ya no lo necesitas)
    /*echo "<pre>DEBUG: Resultados encontrados en asignacionusuariomateria:\n";
    print_r($resultados);
    echo "</pre>";*/

    // Generar botones
    $html = '<h3>Selecciona un grupo:</h3>';
    $html .= '<div style="display: flex; flex-wrap: wrap; gap: 12px;">';

    foreach ($resultados as $fila) {
        $nombre_grupo = $wpdb->get_var($wpdb->prepare(
            "SELECT nombre FROM grupo WHERE id_grupo = %d", $fila->grupo_id
        ));

        $grupo = esc_attr($nombre_grupo);
        $semestre = esc_attr($fila->semestre);
        $url = site_url("/?page_id=1063&grupo=$grupo&semestre=$semestre");

        $html .= "<a href='$url' class='boton-grupo'>$grupo - $semestre</a>";
    }

    $html .= '</div>';

    $html .= "
    <style>
        .boton-grupo {
            background-color: #e60039;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 8px;
            font-family: Arial;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .boton-grupo:hover {
            background-color: #b4002e;
        }
    </style>";

    return $html;
}

