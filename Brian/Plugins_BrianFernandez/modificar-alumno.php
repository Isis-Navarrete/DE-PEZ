<?php
/*
Plugin Name: Modificar Alumno DEPEZ
Description: Permite seleccionar un alumno y modificar su información.
Version: 1.1
Author: Brian Guadalupe Fernández
*/

///////////////////////////
// SHORTCODE DEL FORMULARIO
///////////////////////////

function modificar_alumno_form_shortcode() {
    global $wpdb; // Objeto de base de datos de WordPress

    ob_start(); // Captura de salida HTML para el shortcode

    // Obtener todos los alumnos junto con su grupo y grado actual
    $alumnos = $wpdb->get_results("
        SELECT 
            a.id_alumno, 
            a.nombre, 
            a.apellido_pat, 
            a.apellido_mat, 
            a.correo, 
            g.nombre AS grupo, 
            m.semestre AS grado
        FROM alumno a
        JOIN grupo g ON a.id_grupo = g.id_grupo
        JOIN asignacionmateria am ON a.id_alumno = am.id_alumno
        JOIN materia m ON am.id_materia = m.id_materia
        GROUP BY a.id_alumno
    ");
?>

<!-- Estilos del formulario y la tabla -->
<style>
    .formulario-modificar {
        max-width: 700px;
        background: #f3f3f3;
        padding: 20px;
        border-radius: 10px;
        margin: auto;
    }
    .formulario-modificar input {
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .formulario-modificar button {
        background-color: #FF0000;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .tabla-alumnos {
        margin: 20px auto;
        border-collapse: collapse;
        width: 100%;
    }
    .tabla-alumnos th,
    .tabla-alumnos td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }
    .mensaje-exito {
        color: green;
        font-weight: bold;
    }
    .mensaje-error {
        color: red;
        font-weight: bold;
    }
</style>

<!-- Mostrar mensajes de éxito o error al modificar -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
    <p class="mensaje-exito">✅ Alumno modificado correctamente.</p>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    <p class="mensaje-error">❌ Error: El correo ya existe o los datos son inválidos.</p>
<?php endif; ?>

<!-- Formulario para modificar alumno -->
<form method="post" class="formulario-modificar" id="form_modificar_alumno" onsubmit="return validarModificar()">
    <!-- Campo oculto con el ID del alumno -->
    <input type="hidden" name="id_alumno" id="id_alumno">

    <!-- Campos del formulario -->
    <input type="text" name="nombre" id="nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
    <input type="text" name="apellido_pat" id="apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
    <input type="text" name="apellido_mat" id="apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
    <input type="email" name="correo" id="correo" placeholder="Correo Electrónico" required>
    <input type="number" name="grado" id="grado" placeholder="Grado (Semestre)" required min="1" max="8">
    <input type="text" name="seccion" id="seccion" placeholder="Sección (Grupo)" required pattern="[A-Za-z]+">
    <input type="text" value="ISIC" disabled style="background-color: #eaeaea; font-weight: bold;">

    <button type="submit" name="modificar_alumno">Modificar</button>
</form>

<!-- Tabla con lista de alumnos -->
<table class="tabla-alumnos">
    <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Grupo</th>
        <th>Grado</th>
        <th>Seleccionar</th>
    </tr>
    <?php foreach ($alumnos as $a): ?>
        <tr>
            <td><?= esc_html($a->nombre . ' ' . $a->apellido_pat . ' ' . $a->apellido_mat); ?></td>
            <td><?= esc_html($a->correo); ?></td>
            <td><?= esc_html($a->grupo); ?></td>
            <td><?= esc_html($a->grado); ?></td>
            <td>
                <!-- Botón para llenar el formulario con los datos seleccionados -->
                <button type="button" onclick='llenarFormulario(<?= json_encode($a); ?>)'>Seleccionar</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Script JS para llenar el formulario y validar -->
<script>
    // Llena el formulario con los datos del alumno seleccionado
    function llenarFormulario(alumno) {
        document.getElementById("id_alumno").value = alumno.id_alumno;
        document.getElementById("nombre").value = alumno.nombre;
        document.getElementById("apellido_pat").value = alumno.apellido_pat;
        document.getElementById("apellido_mat").value = alumno.apellido_mat;
        document.getElementById("correo").value = alumno.correo;
        document.getElementById("seccion").value = alumno.grupo;
        document.getElementById("grado").value = alumno.grado;
    }

    // Validación del formulario antes de enviarlo
    function validarModificar() {
        const soloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const grupoRegex = /^[A-Za-z]+$/;

        let errores = "";

        const nombre = document.getElementById("nombre").value;
        const ap1 = document.getElementById("apellido_pat").value;
        const ap2 = document.getElementById("apellido_mat").value;
        const grupo = document.getElementById("seccion").value;

        if (!soloLetras.test(nombre)) errores += "⚠️ El nombre solo puede tener letras.\\n";
        if (!soloLetras.test(ap1)) errores += "⚠️ Apellido paterno inválido.\\n";
        if (!soloLetras.test(ap2)) errores += "⚠️ Apellido materno inválido.\\n";
        if (!grupoRegex.test(grupo)) errores += "⚠️ Sección debe contener solo letras.\\n";

        if (errores !== "") {
            alert(errores);
            return false;
        }
        return true;
    }
</script>

<?php
    return ob_get_clean(); // Devuelve el contenido HTML capturado para el shortcode
}
add_shortcode('formulario_modificar_alumno', 'modificar_alumno_form_shortcode');

///////////////////////////////
// LÓGICA BACKEND: ACTUALIZAR ALUMNO
///////////////////////////////

function procesar_modificacion_alumno() {
    if (isset($_POST['modificar_alumno'])) {
        global $wpdb;

        // Recoger y sanitizar datos del formulario
        $id = intval($_POST['id_alumno']);
        $nombre = sanitize_text_field($_POST['nombre']);
        $ap_pat = sanitize_text_field($_POST['apellido_pat']);
        $ap_mat = sanitize_text_field($_POST['apellido_mat']);
        $correo = sanitize_email($_POST['correo']);
        $grado = intval($_POST['grado']);
        $grupo = sanitize_text_field($_POST['seccion']);

        // Validar que el correo no esté duplicado
        $repetido = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM alumno WHERE correo = %s AND id_alumno != %d",
            $correo, $id
        ));
        if ($repetido > 0) {
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }

        // Obtener el id del grupo
        $id_grupo = $wpdb->get_var($wpdb->prepare("SELECT id_grupo FROM grupo WHERE nombre = %s", $grupo));
        if (!$id_grupo) {
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }

        // Actualizar datos del alumno
        $wpdb->update('alumno', [
            'nombre' => $nombre,
            'apellido_pat' => $ap_pat,
            'apellido_mat' => $ap_mat,
            'correo' => $correo,
            'id_grupo' => $id_grupo
        ], ['id_alumno' => $id]);

        // Eliminar materias actuales
        $wpdb->delete('asignacionmateria', ['id_alumno' => $id]);

        // Obtener nuevas materias según el nuevo grado
        $materias = $wpdb->get_results($wpdb->prepare("SELECT id_materia FROM materia WHERE semestre = %s", $grado));
        $estado = $wpdb->get_var("SELECT id_estado FROM estadoalumno WHERE estado = 'Regular'");

        // Asignar materias nuevamente
        foreach ($materias as $m) {
            $wpdb->insert('asignacionmateria', [
                'id_alumno' => $id,
                'id_materia' => $m->id_materia,
                'id_estado' => $estado
            ]);
        }

        // Redirigir con mensaje de éxito
        wp_redirect(add_query_arg('msg', 'ok'));
        exit;
    }
}
add_action('init', 'procesar_modificacion_alumno'); // Ejecutar función al cargar WordPress
