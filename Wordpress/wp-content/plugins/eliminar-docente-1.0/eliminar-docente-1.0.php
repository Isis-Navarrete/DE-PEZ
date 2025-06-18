<?php
/*
Plugin Name: Eliminar Docente DEPEZ
Description: Permite seleccionar y eliminar docentes desde un formulario y tabla.
Version: 1.0
Author: Brian Guadalupe Fernández
*/

/////////////////////////////////////////////////////
// SHORTCODE: Mostrar formulario y tabla de docentes
/////////////////////////////////////////////////////
function eliminar_docente_shortcode() {
    global $wpdb;
    ob_start(); // Comienza la captura del contenido HTML

    // Consulta SQL: Obtener docentes (excluye jefes de carrera)
    $docentes = $wpdb->get_results("
        SELECT u.id_usuario, u.nombre, u.apellido_pat, u.apellido_mat, u.correo,
               GROUP_CONCAT(r.nombre SEPARATOR ', ') AS roles
        FROM usuario u
        JOIN usuariorol ur ON u.id_usuario = ur.id_usuario
        JOIN rol r ON ur.id_rol = r.id_rol
        WHERE u.id_usuario NOT IN (
            SELECT ur2.id_usuario 
            FROM usuariorol ur2 
            JOIN rol r2 ON ur2.id_rol = r2.id_rol 
            WHERE r2.nombre = 'jefe de carrera'
        )
        GROUP BY u.id_usuario
        HAVING roles NOT LIKE '%jefe de carrera%'
        ORDER BY u.id_usuario ASC, u.apellido_pat, u.apellido_mat, u.nombre
    ");
?>

<!-- ======================== ESTILOS CSS ======================== -->
<style>
    .formulario-eliminar {
        max-width: 600px;
        background: #f3f3f3;
        padding: 20px;
        border-radius: 10px;
        margin: auto;
    }

    .formulario-eliminar input {
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .formulario-eliminar button {
        background-color: #FF0000;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .tabla-docentes {
        width: 100%;
        border-collapse: collapse;
        margin: 30px 0;
    }

    .tabla-docentes th, .tabla-docentes td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }

    .tabla-docentes th {
        background-color: #f8f8f8;
    }

    .mensaje {
        text-align: center;
        font-weight: bold;
        margin: 20px 0;
    }

    .mensaje-exito {
        color: green;
    }

    .mensaje-error {
        color: red;
    }

    .btn-seleccionar {
        background-color: white;
        color: #e91e63;
        padding: 6px 12px;
        border: 1px solid #e91e63;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        font-weight: bold;
    }

    .btn-seleccionar:hover {
        background-color: #e91e63;
        color: white;
    }
</style>

<!-- =================== MENSAJES POST-OPERACIÓN =================== -->
<?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 'ok'): ?>
    <p class="mensaje mensaje-exito">✅ Docente eliminado correctamente.</p>
<?php elseif (isset($_GET['eliminado']) && $_GET['eliminado'] == 'error'): ?>
    <p class="mensaje mensaje-error">❌ Error al eliminar al docente.</p>
<?php endif; ?>

<!-- ============= FORMULARIO DE ELIMINACIÓN ============= -->
<form method="post" class="formulario-eliminar" onsubmit="return confirm('¿Estás seguro de eliminar este docente? Esta acción no se puede deshacer.')">
    <input type="text" name="nombre" id="nombre" placeholder="Nombre" readonly>
    <input type="text" name="apellido_pat" id="apellido_pat" placeholder="Apellido Paterno" readonly>
    <input type="text" name="apellido_mat" id="apellido_mat" placeholder="Apellido Materno" readonly>
    <input type="number" name="id_docente" id="id_docente" placeholder="ID del Docente" required readonly>
    <button type="submit" name="eliminar_docente">Eliminar</button>
</form>

<!-- ============= TABLA DE DOCENTES ============= -->
<table class="tabla-docentes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Correo</th>
            <th>Roles</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($docentes as $d): ?>
            <tr>
                <td><?= esc_html($d->id_usuario); ?></td>
                <td><?= esc_html("{$d->nombre} {$d->apellido_pat} {$d->apellido_mat}"); ?></td>
                <td><?= esc_html($d->correo); ?></td>
                <td><?= esc_html($d->roles); ?></td>
                <td>
                    <!-- Botón para llenar el formulario con los datos del docente -->
                    <button class="btn-seleccionar" onclick='llenarFormulario(<?= json_encode($d); ?>)'>Seleccionar</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- =================== SCRIPT PARA LLENAR FORMULARIO =================== -->
<script>
    function llenarFormulario(docente) {
        document.getElementById("id_docente").value = docente.id_usuario;
        document.getElementById("nombre").value = docente.nombre;
        document.getElementById("apellido_pat").value = docente.apellido_pat;
        document.getElementById("apellido_mat").value = docente.apellido_mat;

        // Desplazar automáticamente al formulario
        document.querySelector('.formulario-eliminar').scrollIntoView({
            behavior: 'smooth'
        });
    }
</script>

<?php
    return ob_get_clean(); // Devuelve todo el HTML generado
}
add_shortcode('formulario_eliminar_docente', 'eliminar_docente_shortcode');

////////////////////////////////////////////////////
// BACKEND: Procesar la eliminación del docente
////////////////////////////////////////////////////
function procesar_eliminacion_docente() {
    if (isset($_POST['eliminar_docente'])) {
        global $wpdb;

        $id_docente = intval($_POST['id_docente']);

        // Iniciar transacción SQL para mantener integridad
        $wpdb->query('START TRANSACTION');

        try {
            // Eliminar relaciones del docente con materias
            $wpdb->delete('asignacionusuariomateria', ['id_usuario' => $id_docente]);

            // Eliminar informes relacionados
            $wpdb->delete('informe', ['id_usuario' => $id_docente]);

            // Eliminar rol asignado al docente
            $wpdb->delete('usuariorol', ['id_usuario' => $id_docente]);

            // Eliminar al docente en sí
            $deleted = $wpdb->delete('usuario', ['id_usuario' => $id_docente]);

            // Confirmar transacción si todo fue exitoso
            if ($deleted) {
                $wpdb->query('COMMIT');
                wp_redirect(add_query_arg('eliminado', 'ok'));
                exit;
            } else {
                throw new Exception('Error al eliminar docente');
            }

        } catch (Exception $e) {
            // Revertir cambios si ocurrió un error
            $wpdb->query('ROLLBACK');
            wp_redirect(add_query_arg('eliminado', 'error'));
            exit;
        }
    }
}
add_action('init', 'procesar_eliminacion_docente');
