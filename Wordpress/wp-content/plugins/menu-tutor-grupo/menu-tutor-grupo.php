<?php
/**
 * Plugin Name: Menú Tutor Grupo Completo
 * Description: Muestra todas las materias que cursa el grupo tutorado, no solo las que imparte el tutor.
 * Version: 1.1
 * Author: Juan Taniz
 */

defined('ABSPATH') || exit;

// Iniciar sesión si no está activa
if (!session_id()) {
    session_start();
}

// Registrar shortcode
add_shortcode('menu_tutor_grupo', 'mostrar_menu_tutor_grupo');

/**
 * Función principal que muestra el menú de materias del grupo tutorado
 * 
 * @return string HTML con el menú de materias del grupo
 */
function mostrar_menu_tutor_grupo() {
    global $wpdb;

    // 1. VALIDAR ACCESO (solo tutores)
    if (!validar_acceso_tutor()) {
        return "<p style='color:red;'>⛔ Debes iniciar sesión como tutor para ver las materias de tu grupo.</p>";
    }

    // 2. OBTENER DATOS DEL TUTOR
    $id_tutor = $_SESSION['usuario_id'];
    $grupo = obtener_grupo_tutorado($id_tutor);
    
    if (!$grupo) {
        return "<p style='color:orange;'>⚠️ Aún no tienes grupo tutorado asignado.</p>";
    }

    // 3. OBTENER SEMESTRE Y MATERIAS
    $semestre = obtener_semestre_grupo($id_tutor);
    $materias = obtener_materias_grupo($semestre);
    
    if (!$materias) {
        return "<p style='color:orange;'>⚠️ El grupo aún no tiene materias asignadas.</p>";
    }

    // 4. GENERAR INTERFAZ
    ob_start();
    ?>
    <div class="tutor-menu-container">
        <h2 class="tutor-menu-title">📘 Materias del grupo <?= esc_html($grupo) ?></h2>

        <div class="tutor-menu-grid">
            <?php foreach ($materias as $materia): ?>
                <a href="<?= generar_url_materia($id_tutor, $grupo, $semestre, $materia) ?>" 
                   class="tutor-menu-item">
                    <?= esc_html(strtoupper($materia->nombre)) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="tutor-menu-footer">
            <a href="javascript:history.back()" class="tutor-menu-back">
                Regresar
            </a>
        </div>
    </div>

    <style>
        .tutor-menu-container {
            font-family: Arial;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .tutor-menu-title {
            color: #1e88e5;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .tutor-menu-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        
        .tutor-menu-item {
            display: inline-block;
            padding: 15px 25px;
            background-color: #e53935;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-family: Arial;
            text-align: center;
            width: 320px;
            transition: all 0.3s ease;
        }
        
        .tutor-menu-item:hover {
            background-color: #c62828;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .tutor-menu-footer {
            margin-top: 40px;
            text-align: center;
        }
        
        .tutor-menu-back {
            display: inline-block;
            padding: 12px 24px;
            background-color: #e53935;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-family: Arial;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .tutor-menu-back:hover {
            background-color: #c62828;
        }
    </style>
    <?php
    
    return ob_get_clean();
}

// ==============================================
// FUNCIONES AUXILIARES
// ==============================================

/**
 * Valida si el usuario es tutor y está logueado
 */
function validar_acceso_tutor() {
    return isset($_SESSION['usuario_id']) && in_array(2, $_SESSION['usuario_roles']);
}

/**
 * Obtiene el nombre del grupo tutorado
 */
function obtener_grupo_tutorado($id_tutor) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT DISTINCT g.nombre 
         FROM asignacionusuariomateria aum 
         INNER JOIN grupo g ON g.id_grupo = aum.id_grupo 
         WHERE aum.id_usuario = %d 
         LIMIT 1",
        $id_tutor
    ));
}

/**
 * Obtiene el semestre del grupo tutorado
 */
function obtener_semestre_grupo($id_tutor) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT DISTINCT aum.semestre 
         FROM asignacionusuariomateria aum 
         INNER JOIN grupo g ON g.id_grupo = aum.id_grupo 
         WHERE aum.id_usuario = %d 
         LIMIT 1",
        $id_tutor
    ));
}

/**
 * Obtiene las materias del semestre del grupo
 */
function obtener_materias_grupo($semestre) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT m.id_materia, m.nombre 
         FROM materia m 
         WHERE m.semestre = %s",
        $semestre
    ));
}

/**
 * Genera la URL para acceder a una materia específica
 */
function generar_url_materia($id_tutor, $grupo, $semestre, $materia) {
    return esc_url(add_query_arg([
        'page_id' => 270,
        'user' => $id_tutor,
        'role' => 2,
        'materia' => $materia->id_materia,
        'grupo' => $grupo,
        'semestre' => $semestre
    ], site_url()));
}
