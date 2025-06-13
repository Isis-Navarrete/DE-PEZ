<?php
/**
 * Plugin Name: Botones por Grupo Docente DEPEZ
 * Description: Muestra solo los grupos y semestres que imparte el docente, redirige con parámetros correctos.
 * Version: 1.2
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
    echo "<pre>DEBUG: ID de sesión detectado: $id_usuario</pre>";

    // Obtener todos los roles del usuario
    $roles = $wpdb->get_col($wpdb->prepare(
        "SELECT id_rol FROM usuariorol WHERE id_usuario = %d", $id_usuario
    ));

    echo "<pre>ID usuario desde sesión: $id_usuario\nRoles detectados:";
    print_r($roles);
    echo "</pre>";

    if (!in_array(1, $roles)) {
        return '<p>Acceso restringido. Solo para docentes.</p>';
    }

    // Obtener los grupos y semestres donde imparte materias
    $resultados = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT am.grupo, am.semestre
         FROM asignacionusuariomateria aum
         INNER JOIN asignacionmateria am ON aum.id_asignacion = am.id_asignacion
         WHERE aum.id_usuario = %d", $id_usuario
    ));

    if (!$resultados) {
        return '<p>No se encontraron grupos asignados para este docente.</p>';
    }

    // Mostrar los botones
    $html = '<h3>Selecciona un grupo:</h3>';
    $html .= '<div style="display: flex; flex-wrap: wrap; gap: 12px;">';

    foreach ($resultados as $fila) {
        $grupo = esc_attr($fila->grupo);
        $semestre = esc_attr($fila->semestre);
        $url = add_query_arg([
            'page_id' => 1063,
            'grupo' => $grupo,
            'semestre' => $semestre
        ], home_url('/'));
        $html .= "<a href='$url' class='boton-grupo'>$grupo - $semestre</a>";
    }

    $html .= '</div>';

    // Estilos básicos
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
    </style>
    ";

    return $html;
}
