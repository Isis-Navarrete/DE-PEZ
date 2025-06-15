<?php
/**
 * Plugin Name: Selector de Grupo y Semestre
 * Description: Genera botones C y D por semestre que redirigen correctamente a la tabla de materias del jefe de carrera.
 * Version: 1.0
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;

add_shortcode('selector_grupo_semestre', 'render_selector_grupo_semestre');

function render_selector_grupo_semestre() {
    $page_id_destino = 277;
    $output = "<h2 style='text-align:center;'>Semestre y Sección</h2>";
    $output .= "<div style='max-width:600px;margin:auto;'>";

    for ($semestre = 1; $semestre <= 8; $semestre++) {
        $output .= "<div style='margin-bottom:20px;padding:10px 0;border-bottom:1px solid #ccc;text-align:center;'>";
        $output .= "<strong>Semestre $semestre</strong><br><br>";

        foreach (['C', 'D'] as $grupo) {
            $url = add_query_arg([
                'page_id' => $page_id_destino,
                'grupo'   => $grupo,
                'semestre'=> $semestre
            ], home_url());

            $output .= "<a href='" . esc_url($url) . "' style='
                display:inline-block;
                margin:0 10px;
                background:#666;
                color:white;
                padding:10px 20px;
                text-decoration:none;
                border-radius:6px;
                font-weight:bold;'>$grupo</a>";
        }

        $output .= "</div>";
    }

    $output .= "</div>";
    return $output;
}
