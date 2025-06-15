<?php
/**
 * Plugin Name: Botón Grupo Tutorado
 * Description: Muestra un botón estilizado para acceder al menú del tutor desde el menú del docente.
 * Version: 1.0
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;

add_shortcode('boton_grupo_tutorado', 'gtt_mostrar_boton');

function gtt_mostrar_boton() {
    $user = isset($_GET['user']) ? intval($_GET['user']) : 0;
    $role = isset($_GET['role']) ? intval($_GET['role']) : 0;

    if ($user > 0 && $role === 2) {
        $url = add_query_arg([
            'page_id' => 88,
            'user'    => $user,
            'role'    => $role
        ], site_url('/'));

        ob_start();
        ?>
        <style>
        .boton-tutorado {
            display: inline-block;
            background-color: #FF0000;
            color: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 16px;
            padding: 14px 30px;
            border: none;
            border-radius: 999px; /* Bordes súper redondeados */
            text-decoration: none;
            text-align: center;
            transition: background 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .boton-tutorado:hover {
            background-color: #cc0000;
        }
        </style>

        <div style="text-align:center; margin: 20px 0;">
            <a href="<?php echo esc_url($url); ?>" class="boton-tutorado">
                Grupo tutorado
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    return '';
}
