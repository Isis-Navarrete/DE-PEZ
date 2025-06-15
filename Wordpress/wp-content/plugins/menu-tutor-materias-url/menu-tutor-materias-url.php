<?php
/**
 * Plugin Name: Menú del Tutor (URL Params)
 * Description: Muestra las materias del grupo tutorado leyendo user_id y role desde la URL GET.
 * Version:     1.0
 * Author:      Juan Taniz
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'menu_tutor_materias', 'mtm_mostrar_materias_via_url' );

function mtm_mostrar_materias_via_url() {
    global $wpdb;

    // 1) Verificar parámetro role=2 (tutor)
    if ( ! isset( $_GET['role'] ) || intval( $_GET['role'] ) !== 2 ) {
        return "<div style='color:red;'><strong>⛔</strong> Debes iniciar sesión como tutor para ver tus materias.</div>";
    }

    // 2) Capturar el ID de usuario del tutor desde la URL
    $id_tutor = isset( $_GET['user'] ) ? intval( $_GET['user'] ) : 0;
    if ( $id_tutor <= 0 ) {
        return "<div style='color:red;'><strong>⛔</strong> Parámetro de usuario inválido.</div>";
    }

    // 3) Consultar materias asignadas al tutor
    $materias = $wpdb->get_results( $wpdb->prepare("
        SELECT 
          m.nombre     AS materia,
          g.nombre     AS grupo,
          aum.semestre AS semestre,
          aum.id_materia
        FROM asignacionusuariomateria aum
        JOIN materia m ON m.id_materia = aum.id_materia
        JOIN grupo   g ON g.id_grupo   = aum.id_grupo
        WHERE aum.id_usuario = %d
    ", $id_tutor ) );

    if ( ! $materias ) {
        return "<div style='color:orange;'>⚠️ No tienes materias asignadas como tutor.</div>";
    }

    // 4) Construir botones
    $html  = "<div style='text-align:center;'><h3>📚 Materias de tu Grupo Tutor</h3>";
    $html .= "<div style='display:flex;flex-wrap:wrap;gap:10px;justify-content:center;'>";

    foreach ( $materias as $m ) {
        $texto = esc_html( $m->materia )
               . " – Grupo " . esc_html( $m->grupo )
               . " (Sem " . esc_html( $m->semestre ) . ")";

        // Génesis de la URL hacia la página 270 (tabla de alertas)
        $url = add_query_arg( [
            'page_id' => 270,
            'user'    => $id_tutor,
            'role'    => 2,
            'materia' => $m->id_materia,
            'grupo'   => $m->grupo,
            'semestre'=> $m->semestre,
        ], site_url( '/' ) );

        $html .= "<a href='{$url}' style='padding:10px 20px;background:#0073aa;color:#fff;text-decoration:none;border-radius:5px;display:inline-block;'>
                    {$texto}
                  </a>";
    }

    $html .= "</div></div>";
    return $html;
}
