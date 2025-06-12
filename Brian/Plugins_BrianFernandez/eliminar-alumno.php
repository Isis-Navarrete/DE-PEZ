<?php
/*
Plugin Name: Eliminar Alumno DEPEZ
Description: Permite seleccionar y eliminar alumnos desde un formulario y tabla.
Version: 1.0
Author: Brian Guadalupe Fernández
*/

/////////////////////////////////////////////
// SHORTCODE: Mostrar formulario y tabla //
/////////////////////////////////////////////

function eliminar_alumno_shortcode() {
    global $wpdb; // Acceso a la base de datos de WordPress

    ob_start(); // Inicia el almacenamiento de contenido HTML

    // Obtener la lista de alumnos con su grupo y semestre
    $alumnos = $wpdb->get_results("
        SELECT a.id_alumno, a.nombre, a.apellido_pat, a.apellido_mat, a.correo, 
               g.nombre AS grupo, m.semestre
        FROM alumno a
        JOIN grupo g ON a.id_grupo = g.id_grupo
        LEFT JOIN asignacionmateria am ON a.id_alumno = am.id_alumno
        LEFT JOIN materia m ON am.id_materia = m.id_materia
        GROUP BY a.id_alumno
    ");
?>

<!-- Estilos CSS del formulario y tabla -->
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

    .tabla-alumnos {
        width: 100%;
        border-collapse: collapse;
        margin: 30px 0;
    }

    .tabla-alumnos th, .tabla-alumnos td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }

    .mensaje {
        text-align: center;
        font-weight: bold;
    }

    .mensaje-exito {
        color: green;
    }

    .mensaje-error {
        color: red;
    }
</style>

<!-- Mostrar mensaje de éxito o error según el resultado de eliminación -->
<?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 'ok'): ?>
    <p class="mensaje mensaje-exito">✅ Alumno eliminado correctamente.</p>
<?php elseif (isset($_GET['eliminado']) && $_GET['eliminado'] == 'error'): ?>
    <p class="mensaje mensaje-error">❌ Error al eliminar al alumno.</p>
<?php endif; ?>

<!-- Formulario para eliminar alumno -->
<form method="post" class="formulario-eliminar" onsubmit="return confirm('¿Estás seguro de eliminar este alumno?')">
    <!-- Campos visibles pero solo lectura -->
    <input type="text" name="nombre" id="nombre" placeholder="Nombre" readonly>
    <input type="text" name="apellido_pat" id="apellido_pat" placeholder="Apellido Paterno" readonly>
    <input type="text" name="apellido_mat" id="apellido_mat" placeholder="Apellido Materno" readonly>
    <input type="number" name="id_alumno" id="id_alumno" placeholder="ID del Alumno" required readonly>

    <!-- Botón para ejecutar eliminación -->
    <button type="submit" name="eliminar_alumno">Eliminar</button>
</form>

<!-- Tabla con todos los alumnos registrados -->
<table class="tabla-alumnos">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Semestre</th>
        <th>Sección</th>
        <th>Seleccionar</th>
    </tr>
    <?php foreach ($alumnos as $a): ?>
        <tr>
            <td><?= $a->id_alumno; ?></td>
            <td><?= esc_html("{$a->nombre} {$a->apellido_pat} {$a->apellido_mat}"); ?></td>
            <td><?= esc_html($a->correo); ?></td>
            <td><?= esc_html($a->semestre); ?></td>
            <td><?= esc_html($a->grupo); ?></td>
            <!-- Botón para llenar el formulario con los datos del alumno -->
            <td><button onclick='llenarFormulario(<?= json_encode($a); ?>)'>Seleccionar</button></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Script JavaScript para llenar los campos del formulario al seleccionar -->
<script>
    function llenarFormulario(alumno) {
        document.getElementById("id_alumno").value = alumno.id_alumno;
        document.getElementById("nombre").value = alumno.nombre;
        document.getElementById("apellido_pat").value = alumno.apellido_pat;
        document.getElementById("apellido_mat").value = alumno.apellido_mat;
    }
</script>

<?php
    return ob_get_clean(); // Devuelve todo el contenido del formulario y tabla
}
add_shortcode('formulario_eliminar_alumno', 'eliminar_alumno_shortcode');

/////////////////////////////////////
// PROCESO BACKEND: ELIMINAR ALUMNO
/////////////////////////////////////

function procesar_eliminacion_alumno() {
    if (isset($_POST['eliminar_alumno'])) {
        global $wpdb;

        $id = intval($_POST['id_alumno']); // ID del alumno a eliminar

        // Eliminar dependencias relacionadas con el alumno
        $wpdb->delete('UnidadMateria', ['id_asigma' => $wpdb->get_col("SELECT id_asigma FROM asignacionmateria WHERE id_alumno = $id")]);
        $wpdb->delete('asignacionmateria', ['id_alumno' => $id]);
        $wpdb->delete('informe', ['id_alumno' => $id]);

        // Finalmente, eliminar el alumno
        $deleted = $wpdb->delete('alumno', ['id_alumno' => $id]);

        // Redireccionar según el resultado
        if ($deleted) {
            wp_redirect(add_query_arg('eliminado', 'ok')); // Mensaje de éxito
            exit;
        } else {
            wp_redirect(add_query_arg('eliminado', 'error')); // Mensaje de error
            exit;
        }
    }
}
add_action('init', 'procesar_eliminacion_alumno');
