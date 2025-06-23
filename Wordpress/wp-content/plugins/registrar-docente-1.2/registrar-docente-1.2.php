<?php
/**
 * Plugin Name: Registro de Docentes DEPEZ
 * Description: Formulario para registrar docentes y asignarles materias por semestre y grupo.
 * Version: 1.2
 * Author: Brian Guadalupe Fernández
 */

/**
 * Función para generar el shortcode del formulario de registro de docentes.
 * Este shortcode mostrará el formulario HTML para ingresar los datos del docente,
 * seleccionar su rol y asignar materias y grupos.
 */
function formulario_registro_docente_shortcode() {
    // Accede a la instancia global de $wpdb para interactuar con la base de datos de WordPress.
    global $wpdb;

    // Obtener los IDs y nombres de los roles 'Profesor' y 'Tutor' de la tabla 'rol'.
    // Esto asegura que se utilicen los IDs correctos de la base de datos.
    $rol_profesor_obj = $wpdb->get_row("SELECT id_rol, nombre FROM rol WHERE nombre = 'Profesor'");
    $rol_tutor_obj = $wpdb->get_row("SELECT id_rol, nombre FROM rol WHERE nombre = 'Tutor'");

    // Asignar los IDs de rol obtenidos, o null si no se encontraron.
    $id_profesor = $rol_profesor_obj ? $rol_profesor_obj->id_rol : null;
    $id_tutor = $rol_tutor_obj ? $rol_tutor_obj->id_rol : null;

    // Preparar las opciones que se mostrarán en el menú desplegable de roles.
    $roles_para_mostrar = [];
    if ($id_profesor !== null) {
        $roles_para_mostrar[] = ['id' => $id_profesor, 'nombre' => 'Profesor'];
    }
    if ($id_profesor !== null && $id_tutor !== null) {
        // Si ambos roles existen, se agrega la opción 'Profesor/Tutor'.
        // El valor de esta opción es una cadena que concatena ambos IDs, separados por una coma.
        $roles_para_mostrar[] = ['id' => "$id_profesor,$id_tutor", 'nombre' => 'Profesor/Tutor'];
    }

    // Obtener todos los grupos de la tabla 'grupo', excluyendo el grupo con id_grupo = 4 (asumiendo que es un grupo no asignable).
    // Los resultados se ordenan por nombre.
    $grupos = $wpdb->get_results("SELECT id_grupo, nombre FROM grupo WHERE id_grupo NOT IN (4) ORDER BY nombre");

    // Obtener todas las materias de la tabla 'materia'.
    // Los resultados se ordenan primero por semestre y luego por nombre.
    $materias = $wpdb->get_results("SELECT id_materia, nombre, semestre FROM materia ORDER BY semestre, nombre");

    // Agrupar las materias obtenidas por su semestre.
    // Esto facilita la visualización y el manejo en el formulario.
    $materias_por_semestre = [];
    foreach ($materias as $m) {
        $materias_por_semestre[$m->semestre][] = $m;
    }

    // Iniciar el búfer de salida para capturar el HTML y CSS.
    ob_start();
    ?>

    <style>
        .form-registro {
            max-width: 700px;
            margin: 0 auto;
            background: #f3f3f3;
            padding: 20px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
        }
        .form-registro input,
        .form-registro select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-registro label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .form-registro button {
            background-color: #FF0000; /* Color de fondo del botón Guardar */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        /* Estilos para el contenedor de botones, usando flexbox para alineación */
        .form-buttons {
            display: flex;
            justify-content: space-between; /* Espacia los botones a los extremos */
            align-items: center;
            margin-top: 20px; /* Margen superior */
        }
        /* Estilos para el botón de regresar (estilizado como un enlace) */
        .form-registro .button-regresar {
            background-color: #FF0000; /* Mismo color que el botón Guardar */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none; /* Elimina el subrayado del enlace */
            display: inline-block; /* Permite que el padding funcione correctamente */
            text-align: center;
        }

        /* Estilos para los mensajes de éxito y error */
        .mensaje-exito {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .mensaje-error {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }
        /* Contenedor para los checkboxes de semestres */
        .semestres-container {
            display: flex;
            flex-wrap: wrap; /* Permite que los elementos se envuelvan a la siguiente línea */
            gap: 10px; /* Espacio entre los elementos */
            margin-bottom: 15px;
        }
        /* Estilos para cada ítem de semestre (checkbox y label) */
        .semestre-item {
            display: flex;
            align-items: center;
            gap: 5px; /* Espacio entre el checkbox y el label */
        }
        /* Contenedor para las materias, con scroll si el contenido es muy largo */
        .materias-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            max-height: 300px; /* Altura máxima */
            overflow-y: auto; /* Habilita el scroll vertical */
        }
        /* Estilos para cada ítem de materia (checkbox, label y select de grupo) */
        .materia-item {
            display: none; /* Inicia oculto, se mostrará con JavaScript */
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee; /* Separador entre materias */
        }
        .materia-item:last-child {
            border-bottom: none; /* Elimina el separador de la última materia */
        }
        /* Estilos para el select de grupo dentro de cada materia */
        .grupo-select {
            margin-left: auto; /* Empuja el select a la derecha */
            min-width: 150px;
        }
    </style>

    <?php
    // Mostrar mensajes de confirmación o error basados en los parámetros de la URL.
    if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
        <p class="mensaje-exito">✅ Docente registrado correctamente.</p>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
        <p class="mensaje-error">❌ Error al registrar el docente. Verifica todos los campos.</p>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'materia_existente'): ?>
        <p class="mensaje-error">❌ Error: Una o más de las materias y grupos seleccionados ya están asignados a otro docente.</p>
    <?php endif; ?>

    <form class="form-registro" method="post" id="form_docente">

        <input type="text" name="nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="email" name="correo" placeholder="Correo Electrónico" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required minlength="6">

        <label for="tipo_docente">Rol:</label>
        <select name="tipo_docente" id="tipo_docente" required>
            <option value="">Seleccionar rol</option>
            <?php foreach ($roles_para_mostrar as $r): ?>
                <option value="<?= esc_attr($r['id']) ?>"><?= esc_html($r['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="mensaje_tutorado" style="display:none; color:#0073aa; font-style: italic; margin: 10px 0;">
            💡 La primera materia seleccionada (incluyendo su semestre y únicamente seleccionando C o D , no ambos) será tomada como el grupo tutorado si el rol incluye Tutor.
        </div>

        <script>
            // Script JavaScript para manejar la visibilidad del mensaje de tutoría.
            document.addEventListener('DOMContentLoaded', function () {
                const rolSelect = document.getElementById('tipo_docente');
                const mensaje = document.getElementById('mensaje_tutorado');

                /**
                 * Muestra u oculta el mensaje de tutoría basado en la selección del rol.
                 * Si el valor del rol incluye una coma (indicando "Profesor/Tutor"), el mensaje se muestra.
                 */
                function mostrarMensaje() {
                    const valor = rolSelect.value.trim();
                    if (valor.includes(',')) {
                        // Si contiene coma, es Profesor/Tutor
                        mensaje.style.display = 'block';
                    } else {
                        // Si no, ocultar el mensaje
                        mensaje.style.display = 'none';
                    }
                }

                // Agrega un event listener para ejecutar la función cuando el rol cambie.
                rolSelect.addEventListener('change', mostrarMensaje);
                // Ejecuta la función al cargar la página para establecer el estado inicial del mensaje.
                mostrarMensaje(); 
            });
        </script>

        <label>Semestres:</label>
        <div class="semestres-container">
            <?php foreach ($materias_por_semestre as $semestre => $materias_semestre_actual): ?>
                <div class="semestre-item">
                    <input type="checkbox" id="semestre_<?= $semestre ?>"
                                 onchange="toggleMaterias(<?= $semestre ?>)"
                                 name="semestres[]" value="<?= $semestre ?>">
                    <label for="semestre_<?= $semestre ?>"><?= $semestre ?>° Semestre</label>
                </div>
            <?php endforeach; ?>
        </div>

        <label>Materias y Grupos:</label>
        <div class="materias-container">
            <?php foreach ($materias_por_semestre as $semestre => $materias_semestre_actual): ?>
                <?php foreach ($materias_semestre_actual as $m): ?>
                    <div class="materia-item" id="materia_<?= $m->id_materia ?>" data-semestre="<?= $semestre ?>">
                        <input type="checkbox" name="materias[]" value="<?= $m->id_materia ?>">
                        <label><?= esc_html($m->nombre) ?> (<?= $m->semestre ?>°)</label>
                        <select name="grupos_<?= $m->id_materia ?>" class="grupo-select">
                            <option value="">Seleccionar grupo</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= esc_attr($g->id_grupo) ?>"><?= esc_html($g->nombre) ?></option>
                            <?php endforeach; ?>
                            <option value="1,2">C/D</option> </select>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div class="form-buttons">
            <button type="submit" name="registrar_docente">Guardar</button>
            <a href="https://nonstop-taniz.mnz.dom.my.id/?page_id=65" class="button-regresar">Regresar</a>
        </div>
    </form>

    <script>
        /**
         * Función JavaScript para mostrar u ocultar las materias de un semestre específico.
         * Se llama cuando se marca/desmarca un checkbox de semestre.
         * @param {number} semestre - El número del semestre a mostrar/ocultar.
         */
        function toggleMaterias(semestre) {
            const checkbox = document.getElementById(`semestre_${semestre}`);
            // Selecciona todas las materias que pertenecen al semestre dado.
            const materias = document.querySelectorAll(`.materia-item[data-semestre="${semestre}"]`);

            materias.forEach(materia => {
                // Si el checkbox del semestre está marcado, muestra la materia; de lo contrario, la oculta.
                materia.style.display = checkbox.checked ? 'flex' : 'none';
                if (!checkbox.checked) {
                    // Si el semestre se desmarca, desmarca también el checkbox de la materia
                    // y resetea la selección del grupo para esa materia.
                    materia.querySelector('input[type="checkbox"]').checked = false;
                    materia.querySelector('select').value = '';
                }
            });
        }

        // Listener para el evento 'submit' del formulario. Realiza validaciones del lado del cliente.
        document.getElementById('form_docente').addEventListener('submit', function(e) {
            const errorMessages = []; // Array para almacenar los mensajes de error.

            // Obtener referencias a los campos del formulario.
            const nombre = this.querySelector('input[name="nombre"]');
            const apellido_pat = this.querySelector('input[name="apellido_pat"]');
            const apellido_mat = this.querySelector('input[name="apellido_mat"]');
            const correo = this.querySelector('input[name="correo"]');
            const contrasena = this.querySelector('input[name="contrasena"]');
            const tipo_docente = this.querySelector('select[name="tipo_docente"]');

            // Validaciones de campos obligatorios y formato.
            if (!nombre.value.trim()) { errorMessages.push('El campo Nombre es obligatorio.'); nombre.style.borderColor = 'red'; } else { nombre.style.borderColor = ''; }
            if (!apellido_pat.value.trim()) { errorMessages.push('El campo Apellido Paterno es obligatorio.'); apellido_pat.style.borderColor = 'red'; } else { apellido_pat.style.borderColor = ''; }
            if (!apellido_mat.value.trim()) { errorMessages.push('El campo Apellido Materno es obligatorio.'); apellido_mat.style.borderColor = 'red'; } else { apellido_mat.style.borderColor = ''; }
            if (!correo.value.trim()) { errorMessages.push('El campo Correo Electrónico es obligatorio.'); correo.style.borderColor = 'red'; } else { correo.style.borderColor = ''; }
            if (contrasena.value.length < 6) { errorMessages.push('La Contraseña debe tener al menos 6 caracteres.'); contrasena.style.borderColor = 'red'; } else { contrasena.style.borderColor = ''; }
            if (!tipo_docente.value) { errorMessages.push('Selecciona un rol de docente.'); tipo_docente.style.borderColor = 'red'; } else { tipo_docente.style.borderColor = ''; }

            // Validar que al menos un semestre esté seleccionado.
            const semestresSeleccionados = this.querySelectorAll('input[name="semestres[]"]:checked').length;
            if (semestresSeleccionados === 0) {
                errorMessages.push('Selecciona al menos un semestre.');
            }

            let materiasValidas = 0; // Contador de materias con grupos válidos.
            let materiasSeleccionadasPeroSinGrupo = 0; // Contador de materias marcadas pero sin grupo seleccionado.

            // Iterar sobre todas las materias marcadas.
            this.querySelectorAll('input[name="materias[]"]:checked').forEach(checkbox => {
                const materiaItem = checkbox.closest('.materia-item');
                // Solo validar si la materia está visible (su semestre está marcado).
                if (materiaItem && materiaItem.style.display !== 'none') {
                    const materiaId = checkbox.value;
                    const grupoSelect = this.querySelector(`select[name="grupos_${materiaId}"]`);

                    if (grupoSelect) {
                        const grupoValue = grupoSelect.value;
                        // Si el grupo es '1,2' (C/D) o un ID de grupo válido (no vacío y no 4).
                        if (grupoValue === '1,2' || (parseInt(grupoValue) > 0 && parseInt(grupoValue) !== 4)) {
                            materiasValidas++;
                        } else if (parseInt(grupoValue) === 4) {
                            // Si el grupo seleccionado es 4 (asumido como no válido).
                            errorMessages.push('El grupo seleccionado para la materia no es válido.');
                        } else {
                            // Si la materia está marcada pero no se ha seleccionado un grupo.
                            materiasSeleccionadasPeroSinGrupo++;
                        }
                    }
                }
            });

            // Mensajes de error específicos para materias y grupos.
            if (materiasValidas === 0 && semestresSeleccionados > 0) {
                errorMessages.push('Selecciona al menos una materia con su grupo correspondiente.');
            }
            if (materiasSeleccionadasPeroSinGrupo > 0) {
                errorMessages.push('Asegúrate de seleccionar un grupo para cada materia marcada.');
            }

            // Si hay errores, prevenir el envío del formulario y mostrar los mensajes.
            if (errorMessages.length > 0) {
                e.preventDefault(); // Detiene el envío del formulario.
                alert('⚠ ' + errorMessages.join('\n⚠ ')); // Muestra una alerta con todos los errores.
            }
        });
    </script>

    <?php
    // Devolver el contenido del búfer de salida (HTML y CSS generados).
    return ob_get_clean();
}
// Registrar el shortcode 'formulario_registro_docente' con la función correspondiente.
add_shortcode('formulario_registro_docente', 'formulario_registro_docente_shortcode');

/**
 * Función para procesar el registro de un docente cuando el formulario es enviado.
 * Se ejecuta cuando se detecta el envío del formulario por el método POST con el botón 'registrar_docente'.
 */
function procesar_registro_docente() {
    if (isset($_POST['registrar_docente'])) {
        global $wpdb; // Accede a la instancia global de $wpdb.

        // Sanitizar y obtener los datos del formulario.
        $nombre = sanitize_text_field($_POST['nombre'] ?? '');
        $ap_pat = sanitize_text_field($_POST['apellido_pat'] ?? '');
        $ap_mat = sanitize_text_field($_POST['apellido_mat'] ?? '');
        $correo = sanitize_email($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? ''; // Contraseña en texto plano
        $rol_seleccionado_value = $_POST['tipo_docente'] ?? ''; // Valor del select del rol
        // Obtener los semestres seleccionados y convertirlos a enteros.
        $semestres = isset($_POST['semestres']) ? array_map('intval', $_POST['semestres']) : [];

        // Validaciones básicas del lado del servidor (redundantes pero importantes por seguridad).
        if (empty($nombre) || empty($ap_pat) || empty($ap_mat) || empty($correo) ||
            strlen($contrasena) < 6 || empty($rol_seleccionado_value) || empty($semestres)) {
            error_log('Validation Error (Server-side): Faltan campos obligatorios o contraseña muy corta.');
            wp_redirect(add_query_arg('msg', 'error')); // Redirige con un mensaje de error genérico.
            exit;
        }

        // Iniciar una transacción de base de datos para asegurar la atomicidad de las operaciones.
        $wpdb->query('START TRANSACTION');

        try {
            // Verificar si el correo electrónico ya existe en la tabla 'usuario'.
            $existing_user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id_usuario FROM usuario WHERE correo = %s",
                $correo
            ));

            if ($existing_user_id) {
                // Si el correo ya existe, lanza una excepción.
                throw new Exception('El correo electrónico ya está registrado.');
            }

            // --- INICIO DE LA NUEVA VALIDACIÓN DE MATERIA/GRUPO YA ASIGNADO ---
            $materias_a_validar = []; // Array para almacenar las combinaciones de materia/grupo a validar.
            foreach ($_POST['materias'] ?? [] as $id_materia) {
                $id_materia = intval($id_materia);
                $grupo_seleccionado_value = isset($_POST["grupos_" . $id_materia]) ? $_POST["grupos_" . $id_materia] : '';
                // Obtener el semestre de la materia desde la base de datos.
                $semestre_materia = $wpdb->get_var($wpdb->prepare("SELECT semestre FROM materia WHERE id_materia = %d", $id_materia));

                $grupos_para_validacion = [];
                // Si se seleccionó "C/D", se consideran los grupos 1 y 2.
                if ($grupo_seleccionado_value === '1,2') {
                    $grupos_para_validacion = [1, 2];
                } elseif (intval($grupo_seleccionado_value) > 0 && intval($grupo_seleccionado_value) !== 4) {
                    // Si es un grupo individual válido (no 0 y no 4).
                    $grupos_para_validacion[] = intval($grupo_seleccionado_value);
                }

                // Añadir cada combinación de materia y grupo a la lista de validación.
                foreach ($grupos_para_validacion as $id_grupo_validar) {
                    $materias_a_validar[] = [
                        'id_materia' => $id_materia,
                        'id_grupo' => $id_grupo_validar,
                        'semestre' => $semestre_materia
                    ];
                }
            }

            // Realizar la validación de si alguna de las materias/grupos ya está asignada.
            if (!empty($materias_a_validar)) {
                foreach ($materias_a_validar as $item) {
                    $materia_asignada_previamente = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM asignacionusuariomateria WHERE id_materia = %d AND id_grupo = %d AND semestre = %d",
                        $item['id_materia'],
                        $item['id_grupo'],
                        $item['semestre']
                    ));

                    if ($materia_asignada_previamente > 0) {
                        // Si la combinación ya existe, lanza una excepción.
                        throw new Exception('Una o más de las materias y grupos seleccionados ya están asignados a otro docente.');
                    }
                }
            }
            // --- FIN DE LA NUEVA VALIDACIÓN ---

            // Insertar los datos del nuevo usuario en la tabla 'usuario'.
            $insert_usuario = $wpdb->insert('usuario', [
                'nombre' => $nombre,
                'apellido_mat' => $ap_mat,
                'apellido_pat' => $ap_pat,
                'correo' => $correo,
                'contrasena' => $contrasena, // La contraseña se guarda en texto plano (considerar hash en producción).
                'telefono' => '' // El campo teléfono se deja vacío por ahora.
            ]);

            // Verificar si la inserción del usuario fue exitosa.
            if ($insert_usuario === false) {
                throw new Exception('Error al insertar usuario: ' . $wpdb->last_error);
            }

            $id_usuario = $wpdb->insert_id; // Obtener el ID del usuario recién insertado.

            // Asignar los roles seleccionados al usuario en la tabla 'usuariorol'.
            // Los roles pueden venir como una cadena separada por comas (ej. "1,2").
            $roles_a_insertar = explode(',', $rol_seleccionado_value);
            foreach ($roles_a_insertar as $id_rol) {
                $id_rol = intval($id_rol);
                // Solo se permite la asignación de roles 1 (Profesor) y 2 (Tutor).
                if ($id_rol === 1 || $id_rol === 2) {
                    $wpdb->insert('usuariorol', [
                        'id_usuario' => $id_usuario,
                        'id_rol' => $id_rol
                    ]);
                } else {
                    error_log("Attempted to assign disallowed role ID: $id_rol during registration for user $id_usuario.");
                }
            }

            // Procesar las asignaciones de materias y grupos.
            $materias_asignadas_count = 0; // Contador de materias asignadas correctamente.
            $any_materia_checkbox_checked = false; // Bandera para saber si se marcó al menos un checkbox de materia.

            foreach ($_POST['materias'] ?? [] as $id_materia) {
                $any_materia_checkbox_checked = true; // Se marcó al menos un checkbox de materia.
                $id_materia = intval($id_materia);
                $grupo_seleccionado_value = isset($_POST["grupos_" . $id_materia]) ? $_POST["grupos_" . $id_materia] : '';

                // Obtener el semestre de la materia.
                $semestre = $wpdb->get_var($wpdb->prepare(
                    "SELECT semestre FROM materia WHERE id_materia = %d",
                    $id_materia
                ));

                if ($semestre === null) {
                    error_log("Attempted to assign non-existent materia ID: $id_materia");
                    continue; // Saltar a la siguiente materia si el ID no existe.
                }

                // Determinar los grupos a insertar (manejar la opción "C/D").
                $grupos_a_insertar = [];
                if ($grupo_seleccionado_value === '1,2') { // Si se seleccionó C/D
                    $grupos_a_insertar = [1, 2]; // Asume que ID 1 es 'C' y ID 2 es 'D'
                } elseif (intval($grupo_seleccionado_value) > 0 && intval($grupo_seleccionado_value) !== 4) { // Si es un grupo individual válido (no 4)
                    $grupos_a_insertar[] = intval($grupo_seleccionado_value);
                }

                // Insertar las asignaciones de materia y grupo en la tabla 'asignacionusuariomateria'.
                if (!empty($grupos_a_insertar)) {
                    foreach ($grupos_a_insertar as $id_grupo) {
                        $insert_asignacion = $wpdb->insert('asignacionusuariomateria', [
                            'id_usuario' => $id_usuario,
                            'id_materia' => $id_materia,
                            'id_grupo' => $id_grupo,
                            'semestre' => $semestre
                        ]);

                        if ($insert_asignacion === false) {
                            throw new Exception('Error al asignar materia y grupo: ' . $wpdb->last_error);
                        } else {
                            $materias_asignadas_count++; // Incrementar el contador de materias asignadas.
                        }
                    }
                }
            }

            // Validar si se seleccionaron semestres pero no se asignó ninguna materia con grupo válido.
            if (!empty($semestres) && $materias_asignadas_count === 0 && $any_materia_checkbox_checked) {
                throw new Exception('Se seleccionaron semestres pero no se asignaron materias válidas o los grupos seleccionados no son permitidos.');
            }

            // Si todas las operaciones fueron exitosas, confirmar la transacción.
            $wpdb->query('COMMIT');
            wp_redirect(add_query_arg('msg', 'ok')); // Redirige con un mensaje de éxito.
            exit;

        } catch (Exception $e) {
            // Si ocurre algún error, revertir la transacción para deshacer los cambios.
            $wpdb->query('ROLLBACK');
            error_log('Error en registro de docente: ' . $e->getMessage()); // Registrar el error.

            // Redirigir con un mensaje de error específico según la causa.
            if ($e->getMessage() === 'Una o más de las materias y grupos seleccionados ya están asignados a otro docente.') {
                wp_redirect(add_query_arg('msg', 'materia_existente'));
            } else {
                wp_redirect(add_query_arg('msg', 'error'));
            }
            exit;
        }
    }
}
// Enganchar la función `procesar_registro_docente` al hook 'init' de WordPress.
// Esto asegura que la función se ejecute al inicio de cada carga de página de WordPress.
add_action('init', 'procesar_registro_docente');