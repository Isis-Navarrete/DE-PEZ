<?php
/*
Plugin Name: Registro de Alumnos DEPEZ
Description: Formulario para registrar alumnos con validación de campos.
Version: 1.1
Author: Brian Guadalupe Fernández
*/

///////////////////////////////////////////
// SHORTCODE PARA MOSTRAR EL FORMULARIO //
///////////////////////////////////////////

function formulario_registro_alumno_shortcode() {
    ob_start(); // Inicia el almacenamiento del contenido HTML

    // Aquí se incluye CSS embebido para darle estilo al formulario
    ?>

    <style>
        .form-registro {
            max-width: 600px;
            margin: 0 auto;
            background: #f3f3f3;
            padding: 20px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
        }

        .form-registro input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-registro button {
            background-color: #FF0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .mensaje-error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        .mensaje-exito {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>

    <!-- Formulario de registro -->
    <form class="form-registro" method="post" id="form_registro_alumno" onsubmit="return validarFormulario()">
        <!-- Campos de entrada -->
        <input type="text" name="nombre" id="nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_pat" id="apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_mat" id="apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="email" name="correo" id="correo" placeholder="Correo Electrónico" required>
        <input type="number" name="grado" id="grado" placeholder="Grado (Semestre)" required min="1" max="8">
        <input type="text" name="seccion" id="seccion" placeholder="Sección (Grupo)" required pattern="[A-Za-z]+" title="Solo letras">
        <input type="text" value="ISIC" disabled> <!-- Campo de carrera fija -->

        <button type="submit" name="registrar_alumno">Guardar</button>

        <!-- Mostrar mensajes después de enviar -->
        <?php if (isset($_POST['registrar_alumno']) && isset($_POST['mensaje_form'])): ?>
            <div class="<?= esc_attr($_POST['mensaje_form_tipo']); ?>">
                <?= esc_html($_POST['mensaje_form']); ?>
            </div>
        <?php endif; ?>

        <div id="mensaje_error" class="mensaje-error" style="display:none;"></div>
    </form>

    <!-- Validación del formulario en el navegador -->
    <script>
        function validarFormulario() {
            const soloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+$/;
            const soloLetrasGrupo = /^[A-Za-z]+$/;

            const nombre = document.getElementById('nombre').value;
            const apPat = document.getElementById('apellido_pat').value;
            const apMat = document.getElementById('apellido_mat').value;
            const grupo = document.getElementById('seccion').value;

            let mensaje = "";

            if (!soloLetras.test(nombre)) mensaje += "⚠️ El nombre solo puede contener letras.\\n";
            if (!soloLetras.test(apPat)) mensaje += "⚠️ El apellido paterno solo puede contener letras.\\n";
            if (!soloLetras.test(apMat)) mensaje += "⚠️ El apellido materno solo puede contener letras.\\n";
            if (!soloLetrasGrupo.test(grupo)) mensaje += "⚠️ La sección solo puede contener letras.\\n";

            if (mensaje) {
                document.getElementById('mensaje_error').innerText = mensaje;
                document.getElementById('mensaje_error').style.display = 'block';
                return false;
            }

            return true;
        }
    </script>

    <?php
    return ob_get_clean(); // Devuelve el contenido del formulario para insertarlo donde se use el shortcode
}
add_shortcode('formulario_registro_alumno', 'formulario_registro_alumno_shortcode'); // Registrar el shortcode


///////////////////////////////////////////
// PROCESO BACKEND: GUARDAR ALUMNO EN BD //
///////////////////////////////////////////

function registrar_alumno_proceso() {
    if (isset($_POST['registrar_alumno'])) {
        global $wpdb; // Objeto de base de datos de WordPress

        // Obtener y sanitizar datos del formulario
        $nombre = sanitize_text_field($_POST['nombre']);
        $apellido_pat = sanitize_text_field($_POST['apellido_pat']);
        $apellido_mat = sanitize_text_field($_POST['apellido_mat']);
        $correo = sanitize_email($_POST['correo']);
        $grado = intval($_POST['grado']);
        $seccion = sanitize_text_field($_POST['seccion']);

        // Validación de servidor (por seguridad)
        if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+$/", $nombre) ||
            !preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+$/", $apellido_pat) ||
            !preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+$/", $apellido_mat) ||
            !preg_match("/^[A-Za-z]+$/", $seccion)) {
            $_POST['mensaje_form'] = "❌ Error: Datos inválidos enviados.";
            $_POST['mensaje_form_tipo'] = "mensaje-error";
            return;
        }

        // Obtener IDs necesarios
        $id_grupo = $wpdb->get_var($wpdb->prepare("SELECT id_grupo FROM grupo WHERE nombre = %s", $seccion));
        $estado_regular = $wpdb->get_var("SELECT id_estado FROM estadoalumno WHERE estado = 'Regular'");

        // Verificar que existan los valores requeridos
        if (!$id_grupo || !$estado_regular) {
            $_POST['mensaje_form'] = "❌ Grupo o estado no encontrados.";
            $_POST['mensaje_form_tipo'] = "mensaje-error";
            return;
        }

        // Insertar alumno en la tabla
        $wpdb->insert('alumno', [
            'nombre' => $nombre,
            'apellido_mat' => $apellido_mat,
            'apellido_pat' => $apellido_pat,
            'correo' => $correo,
            'id_grupo' => $id_grupo
        ]);

        // Obtener ID del alumno recién insertado
        $id_alumno = $wpdb->insert_id;

        // Buscar materias por semestre y asignarlas automáticamente al alumno
        $materias = $wpdb->get_results($wpdb->prepare("SELECT id_materia FROM materia WHERE semestre = %s", $grado));
        foreach ($materias as $m) {
            $wpdb->insert('asignacionmateria', [
                'id_alumno' => $id_alumno,
                'id_materia' => $m->id_materia,
                'id_estado' => $estado_regular
            ]);
        }

        // Confirmar éxito al usuario
        $_POST['mensaje_form'] = "✅ Alumno registrado correctamente.";
        $_POST['mensaje_form_tipo'] = "mensaje-exito";
    }
}
add_action('init', 'registrar_alumno_proceso'); // Ejecutar la lógica al cargar WordPress
