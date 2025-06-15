<?php
/**
 * Plugin Name: DEPEZ - Tabla de Profesores (Selección)
 * Description: Muestra una tabla con los profesores y permite rellenar un formulario Forminator al seleccionar uno.
 * Version: 1.0
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;

add_shortcode('tabla_profesores_forminator', 'tabla_profesores_forminator_handler');

function tabla_profesores_forminator_handler() {
    global $wpdb;

    // Obtener solo los usuarios con rol de profesor (id_rol = 1)
    $profesores = $wpdb->get_results("
        SELECT u.id_usuario, u.nombre, u.apellido_pat, u.apellido_mat, u.correo, u.telefono
        FROM usuario u
        JOIN usuariorol ur ON ur.id_usuario = u.id_usuario
        WHERE ur.id_rol = 1
        ORDER BY u.nombre ASC
    ");

    if (!$profesores) return "<div style='color:orange;'>⚠️ No hay profesores registrados.</div>";

    ob_start(); ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
        <thead style="background:#ddd;">
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($profesores as $p):
            $nombre_completo = "{$p->nombre} {$p->apellido_pat} {$p->apellido_mat}";
            ?>
            <tr>
                <td><?= esc_html($p->id_usuario) ?></td>
                <td><?= esc_html($nombre_completo) ?></td>
                <td><?= esc_html($p->correo) ?></td>
                <td><?= esc_html($p->telefono) ?></td>
                <td>
                    <button onclick='llenarFormularioProfesor(<?= json_encode([
                        'id' => $p->id_usuario,
                        'nombre' => $p->nombre,
                        'apellido_pat' => $p->apellido_pat,
                        'apellido_mat' => $p->apellido_mat,
                        'correo' => $p->correo,
                        'telefono' => $p->telefono
                    ]) ?>)'>Seleccionar</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    function llenarFormularioProfesor(data) {
        const campos = {
            'name-1': data.nombre,
            'name-2': data.apellido_pat,
            'name-3': data.apellido_mat,
            'email-1': data.correo,
            'phone-1': data.telefono,
            'hidden-1': data.id
        };

        Object.entries(campos).forEach(([campo, valor]) => {
            const input = document.querySelector(`[name="${campo}"]`);
            if (input) input.value = valor;
        });
    }
    </script>
    <?php
    return ob_get_clean();
}
