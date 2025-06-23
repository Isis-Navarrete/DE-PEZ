<?php
/**
 * Plugin Name: Tabla Materias por Grupo
 * Description: Muestra las materias por grupo y semestre para jefes de carrera. Permite acceder a la tabla de alertas.
 * Version: 1.2
 * Author: Juan Taniz
 */

// Seguridad: Salir si se accede directamente
if (!defined('ABSPATH')) exit;

// Iniciar sesión si no está activa
if (!session_id()) session_start();

// Registrar shortcode
add_shortcode('materias_por_grupo', 'mostrar_materias_por_grupo');

function mostrar_materias_por_grupo() {
    global $wpdb;

    // Verificar parámetros requeridos en la URL
    if (!isset($_GET['grupo'], $_GET['semestre'])) {
        return "<div style='color:orange;'>⚠️ Falta información en la URL (grupo y semestre).</div>";
    }

    // Sanitizar datos de entrada
    $grupo_nombre = sanitize_text_field($_GET['grupo']);
    $semestre = sanitize_text_field($_GET['semestre']);

    // Obtener ID del grupo
    $id_grupo = $wpdb->get_var($wpdb->prepare(
        "SELECT id_grupo FROM grupo WHERE nombre = %s", 
        $grupo_nombre
    ));

    if (!$id_grupo) {
        return "<div style='color:red;'>❌ El grupo especificado no existe.</div>";
    }

    // Obtener materias del semestre
    $materias = $wpdb->get_results($wpdb->prepare(
        "SELECT id_materia, nombre FROM materia WHERE semestre = %s",
        $semestre
    ));

    if (!$materias) {
        return "<div style='color:orange;'>⚠️ No hay materias registradas para el semestre $semestre.</div>";
    }

    ob_start(); // Iniciar buffer de salida

    // Mostrar título
    echo "<h2 style='color:skyblue;font-family:Arial;'>📘 Materias del grupo $grupo_nombre - Semestre $semestre</h2>";

    // Mostrar lista de materias
    foreach ($materias as $materia) {
        // Buscar profesor asignado
        $profesor = $wpdb->get_var($wpdb->prepare(
            "SELECT CONCAT(u.nombre, ' ', u.apellido_pat) 
             FROM asignacionusuariomateria aum 
             JOIN usuario u ON u.id_usuario = aum.id_usuario 
             WHERE aum.id_materia = %d 
             AND aum.id_grupo = %d 
             AND aum.semestre = %s 
             LIMIT 1",
            $materia->id_materia,
            $id_grupo,
            $semestre
        ));

        if (!$profesor) $profesor = "Sin asignar";

        // Construir URL para tabla de alertas
        $link = add_query_arg([
            'page_id' => 270,
            'materia' => $materia->id_materia,
            'grupo' => $grupo_nombre,
            'semestre'=> $semestre
        ], site_url('/'));

        // Mostrar materia y profesor
        echo "<div style='margin:10px 0;'>
            <a href='$link' style='display:inline-block;padding:10px 20px;margin:5px; background:#FF0000;color:#FFFFFF;text-decoration:none; font-family:Arial;font-weight:bold;border-radius:20px;'>
                {$materia->nombre}
            </a><br>
            <span style='font-family:Arial;font-size:14px;'>👨‍🏫 Profesor: $profesor</span>
        </div>";
    }

    // Botón para regresar
    echo "<div style='margin-top:30px;'>
        <a href='javascript:history.back()' style='display:inline-block; padding:10px 20px; background-color:#e60000; color:white; font-family:Arial, sans-serif; text-decoration:none; border-radius:6px; font-weight:bold;'>
            Regresar
        </a>
    </div>";

    return ob_get_clean();
}