<?php
/**
 * Plugin Name: Menú Tutor Grupo Completo
 * Description: Muestra todas las materias que cursa el grupo tutorado, no solo las que imparte el tutor.
 * Version: 1.0
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;
if (!session_id()) session_start();

add_shortcode('menu_tutor_grupo', 'mostrar_menu_tutor_grupo');

function mostrar_menu_tutor_grupo() {
    global $wpdb;

    // Verificar si hay sesión y es tutor
    if (!isset($_SESSION['usuario_id']) || !in_array(2, $_SESSION['usuario_roles'])) {
        return "<p style='color:red;'>⛔ Debes iniciar sesión como tutor para ver las materias de tu grupo.</p>";
    }

    $id_tutor = $_SESSION['usuario_id'];

    // Obtener el grupo tutorado (grupo al que el tutor imparte al menos una materia)
    $grupo = $wpdb->get_var($wpdb->prepare("
        SELECT DISTINCT g.nombre
        FROM asignacionusuariomateria aum
        INNER JOIN grupo g ON g.id_grupo = aum.id_grupo
        WHERE aum.id_usuario = %d
        LIMIT 1
    ", $id_tutor));

    if (!$grupo) {
        return "<p style='color:orange;'>⚠️ Aún no tienes grupo tutorado asignado.</p>";
    }

    // Obtener el semestre del grupo tutorado
    $semestre = $wpdb->get_var($wpdb->prepare("
        SELECT DISTINCT aum.semestre
        FROM asignacionusuariomateria aum
        INNER JOIN grupo g ON g.id_grupo = aum.id_grupo
        WHERE aum.id_usuario = %d
        LIMIT 1
    ", $id_tutor));

    // Obtener todas las materias del semestre del grupo (sin importar el profesor)
    $materias = $wpdb->get_results($wpdb->prepare("
        SELECT m.id_materia, m.nombre
        FROM materia m
        WHERE m.semestre = %s
    ", $semestre));

    if (!$materias) {
        return "<p style='color:orange;'>⚠️ El grupo aún no tiene materias asignadas.</p>";
    }

    ob_start();
    ?>
    <div style="font-family: Arial;">
        <h2 style="color:skyblue;">📘 Materias del grupo <?= esc_html($grupo) ?></h2>

        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <?php foreach ($materias as $materia): ?>
                <a href="<?= esc_url(add_query_arg([
                    'page_id' => 270,
                    'user' => $id_tutor,
                    'role' => 2,
                    'materia' => $materia->id_materia,
                    'grupo' => $grupo,
                    'semestre' => $semestre
                ], site_url())) ?>"
                   style="
                       display: inline-block;
                       padding: 15px 25px;
                       background-color: #FF0000;
                       color: #FFFFFF;
                       text-decoration: none;
                       border-radius: 25px;
                       font-weight: bold;
                       font-family: Arial;
                       text-align: center;
                       width: 320px;
                   ">
                    <?= esc_html(strtoupper($materia->nombre)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
