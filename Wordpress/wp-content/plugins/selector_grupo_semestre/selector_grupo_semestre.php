<?php
/**
 * Plugin Name: Selector de Grupo y Semestre
 * Description: Genera botones C y D por semestre que redirigen correctamente a la tabla de materias del jefe de carrera.
 * Version: 1.1
 * Author: Juan Taniz
 */

// Seguridad: Salir si se accede directamente
if (!defined('ABSPATH')) exit;

// Registrar shortcode
add_shortcode('selector_grupo_semestre', 'render_selector_grupo_semestre');

function render_selector_grupo_semestre() {
    // ID de la página de destino para las materias
    $page_id_destino = 277;
    
    // Iniciar construcción del HTML
    $output = "<h2 style='text-align:center;'>Semestre y Sección</h2>";
    $output .= "<div style='max-width:600px;margin:auto;'>";
    
    // Generar botones para cada semestre (1-8)
    for ($semestre = 1; $semestre <= 8; $semestre++) {
        $output .= "<div style='margin-bottom:20px;padding:10px 0;border-bottom:1px solid #ccc;text-align:center;'>";
        $output .= "<strong>Semestre $semestre</strong><br><br>";
        
        // Generar botones C y D para cada semestre
        foreach (['C', 'D'] as $grupo) {
            // Construir URL con parámetros
            $url = add_query_arg([
                'page_id' => $page_id_destino,
                'grupo' => $grupo,
                'semestre'=> $semestre
            ], home_url());
            
            // Botón del grupo
            $output .= "<a href='" . esc_url($url) . "' style='display:inline-block; margin:0 10px; background:#666; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold;'>$grupo</a>";
        }
        
        $output .= "</div>";
    }
    
    $output .= "</div>";
    
    // Botón de regreso
    $output .= "<div style='text-align:center; margin-top:30px;'>
        <a href='javascript:history.back()' style='display:inline-block; padding:10px 20px; background-color:#e60000; color:white; font-family:Arial, sans-serif; text-decoration:none; border-radius:6px; font-weight:bold;'>
            Regresar
        </a>
    </div>";
    
    return $output;
}