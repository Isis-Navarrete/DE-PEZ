<?php
/**
 * Plugin Name: Editar Docente DEPEZ
 * Description: Permite seleccionar y editar docentes, incluyendo roles, materias y grupos.
 * Version: 1.1
 * Author: Brian Guadalupe Fernández
 */

/**
 * Shortcode para mostrar el formulario de edición y la tabla de docentes.
 * Este shortcode generará el HTML para el formulario de edición de docentes y
 * una tabla con la lista de docentes existentes para seleccionar.
 */
function formulario_editar_docente_shortcode() {
    global $wpdb; // Accede a la instancia global de $wpdb para interactuar con la base de datos de WordPress.

    // Obtener los IDs y nombres de los roles 'Profesor' y 'Tutor' de la tabla 'rol'.
    // Los IDs (1 y 2) se asumen para estos roles.
    $roles_db = $wpdb->get_results("SELECT id_rol, nombre FROM rol WHERE id_rol IN (1, 2)");
    $id_profesor = null;
    $id_tutor = null;

    // Asignar los IDs a variables para facilitar su uso.
    foreach ($roles_db as $r) {
        if ($r->nombre === 'Profesor') {
            $id_profesor = $r->id_rol;
        } elseif ($r->nombre === 'Tutor') {
            $id_tutor = $r->id_rol;
        }
    }

    // Preparar las opciones de rol que se mostrarán en el menú desplegable del formulario de edición.
    $roles_para_mostrar = [];
    if ($id_profesor !== null) {
        $roles_para_mostrar[] = ['id' => $id_profesor, 'nombre' => 'Profesor'];
    }
    if ($id_profesor !== null && $id_tutor !== null) {
        // Se crea una opción combinada 'Profesor/Tutor' con los IDs concatenados por una coma.
        $roles_para_mostrar[] = ['id' => "$id_profesor,$id_tutor", 'nombre' => 'Profesor/Tutor'];
    }

    // Obtener todos los grupos de la tabla 'grupo', excluyendo el grupo con id_grupo = 4 (asumido como no asignable).
    $grupos = $wpdb->get_results("SELECT id_grupo, nombre FROM grupo WHERE id_grupo != 4 ORDER BY nombre");

    // Obtener todas las materias para agruparlas por semestre.
    $materias_todas = $wpdb->get_results("SELECT id_materia, nombre, semestre FROM materia ORDER BY semestre, nombre");
    $materias_por_semestre = [];
    // Agrupar las materias en un array asociativo donde la clave es el semestre.
    foreach ($materias_todas as $m) {
        $materias_por_semestre[$m->semestre][] = $m;
    }

    // --- Obtener datos de los docentes para la tabla ---
    // Consulta SQL para obtener la información de los docentes, sus roles asignados y
    // las materias con grupos que tienen asignadas.
    $docentes = $wpdb->get_results("
        SELECT
            u.id_usuario,
            u.nombre,
            u.apellido_pat,
            u.apellido_mat,
            u.correo,
            GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS roles, -- Concatena los nombres de los roles
            GROUP_CONCAT(DISTINCT ur.id_rol ORDER BY ur.id_rol SEPARATOR ',') AS ids_roles_asignados, -- Concatena los IDs de los roles
            (
                SELECT JSON_ARRAYAGG( -- Agrupa las materias asignadas como un array JSON
                    JSON_OBJECT(
                        'id_materia', aum.id_materia,
                        'id_grupo', aum.id_grupo,
                        'semestre', aum.semestre,
                        'nombre_materia', m.nombre,
                        'nombre_grupo', g.nombre
                    )
                )
                FROM asignacionusuariomateria aum
                JOIN materia m ON aum.id_materia = m.id_materia
                JOIN grupo g ON aum.id_grupo = g.id_grupo
                WHERE aum.id_usuario = u.id_usuario
            ) AS materias_asignadas_json
        FROM usuario u
        JOIN usuariorol ur ON u.id_usuario = ur.id_usuario
        JOIN rol r ON ur.id_rol = r.id_rol
        WHERE u.id_usuario NOT IN (
            SELECT ur2.id_usuario
            FROM usuariorol ur2
            JOIN rol r2 ON ur2.id_rol = r2.id_rol
            WHERE r2.nombre = 'Jefe de Carrera' -- Excluye usuarios con el rol 'Jefe de Carrera'
        )
        GROUP BY u.id_usuario, u.nombre, u.apellido_pat, u.apellido_mat, u.correo
        HAVING roles NOT LIKE '%Jefe de Carrera%' -- Doble verificación para excluir Jefes de Carrera
        ORDER BY u.id_usuario ASC, u.apellido_pat, u.apellido_mat, u.nombre
    ");

    ob_start(); // Inicia el búfer de salida para capturar el HTML y CSS.
    ?>

    <style>
        .form-edicion, .formulario-eliminar {
            max-width: 700px;
            margin: 0 auto;
            background: #f3f3f3;
            padding: 20px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
            margin-bottom: 30px; /* Espacio debajo del formulario */
        }
        .form-edicion input,
        .form-edicion select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-edicion label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .form-actions { /* Estilo para el contenedor de botones en el formulario de edición */
            display: flex;
            justify-content: space-between; /* Alinea los botones a los extremos */
            align-items: center;
            margin-top: 20px;
        }
        .form-edicion button {
            background-color: #FF0000; /* Color de fondo del botón Modificar */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-edicion .button-regresar { /* Estilo para el botón de regresar */
            background-color: #FF0000; /* Mismo color que el de modificar */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none; /* Elimina el subrayado de enlace */
            display: inline-block; /* Permite aplicar padding y margin */
            text-align: center;
        }
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
        .semestres-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .semestre-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .materias-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        .materia-item {
            display: none; /* Inicialmente oculto, se muestra con JS */
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .materia-item:last-child {
            border-bottom: none;
        }
        .grupo-select {
            margin-left: auto;
            min-width: 150px;
        }
        .tabla-docentes {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-family: Arial, sans-serif;
        }
        .tabla-docentes th, .tabla-docentes td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .tabla-docentes th {
            background-color: #f8f8f8;
            font-weight: bold;
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

    <?php
    // Mostrar mensajes de confirmación o error basados en los parámetros de la URL.
    if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
        <p class="mensaje-exito">✅ Docente actualizado correctamente.</p>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
        <p class="mensaje-error">❌ Error al actualizar el docente. Verifica todos los campos.</p>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'materia_duplicada'): ?>
        <p class="mensaje-error">❌ Error: Una o más materias con su grupo ya están asignadas a otro docente. Por favor, revisa las asignaciones.</p>
    <?php endif; ?>

    <form class="form-edicion" method="post" id="form_editar_docente">
        <input type="hidden" name="id_usuario" id="edit_id_usuario" required>

        <input type="text" name="nombre" id="edit_nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_pat" id="edit_apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_mat" id="edit_apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="email" name="correo" id="edit_correo" placeholder="Correo Electrónico" required>
        
        <label for="edit_rol">Rol:</label>
        <select name="rol_seleccionado" id="edit_rol" required onchange="manejarCambioRol()">
            <option value="">Seleccionar rol</option>
            <?php foreach ($roles_para_mostrar as $r): ?>
                <option value="<?= esc_attr($r['id']) ?>"><?= esc_html($r['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <div id="mensaje_tutor_materias" style="display:none; color:#0073aa; font-style: italic; margin: 10px 0;">
            💡 Si el docente tiene el rol de tutor y se modifican las materias, el grupo tutorado será asignado tomando en cuenta el grupo y semestre de la primera materia que aparezca en la tabla.
        </div>

        <label>Semestres:</label>
        <div class="semestres-container">
            <?php foreach ($materias_por_semestre as $semestre => $materias_semestre_actual): ?>
                <div class="semestre-item">
                    <input type="checkbox" id="edit_semestre_<?= $semestre ?>"
                            onchange="toggleMateriasPorSemestreEdicion(<?= $semestre ?>)"
                            name="semestres[]" value="<?= $semestre ?>">
                    <label for="edit_semestre_<?= $semestre ?>"><?= $semestre ?>° Semestre</label>
                </div>
            <?php endforeach; ?>
        </div>

        <label>Materias y Grupos:</label>
        <div class="materias-container">
            <?php foreach ($materias_por_semestre as $semestre => $materias_semestre_actual): ?>
                <?php foreach ($materias_semestre_actual as $m): ?>
                    <div class="materia-item materia-semestre-<?= $semestre ?>" id="edit_materia_<?= $m->id_materia ?>">
                        <input type="checkbox" name="materias[]" value="<?= $m->id_materia ?>">
                        <label><?= esc_html($m->nombre) ?> (<?= esc_html($m->semestre) ?>° Semestre)</label>
                        <select name="grupos_<?= $m->id_materia ?>" class="grupo-select">
                            <option value="">Seleccionar grupo</option>
                            <option value="1,2">C/D</option> <?php foreach ($grupos as $g): ?>
                                <option value="<?= esc_attr($g->id_grupo) ?>"><?= esc_html($g->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="actualizar_docente">Modificar</button>
            <a href="https://nonstop-taniz.mnz.dom.my.id/?page_id=65" class="button-regresar">Regresar</a>
        </div>
    </form>

    <table class="tabla-docentes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Roles</th>
                <th>Materias Asignadas (Semestre - Grupo)</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($docentes as $d): // Itera sobre cada docente para mostrarlo en la tabla.
                // Decodifica la cadena JSON de materias asignadas a un array PHP.
                $materias_asignadas_data = json_decode($d->materias_asignadas_json, true);
                $materias_info = [];
                if (is_array($materias_asignadas_data)) {
                    foreach ($materias_asignadas_data as $ma) {
                        // Formatea la información de la materia para mostrarla.
                        $materias_info[] = esc_html("{$ma['nombre_materia']} ({$ma['semestre']}° - {$ma['nombre_grupo']})");
                    }
                }
            ?>
                <tr>
                    <td><?= esc_html($d->id_usuario); ?></td>
                    <td><?= esc_html("{$d->nombre} {$d->apellido_pat} {$d->apellido_mat}"); ?></td>
                    <td><?= esc_html($d->correo); ?></td>
                    <td><?= esc_html($d->roles); ?></td>
                    <td><?= implode('<br>', $materias_info); ?></td> <td>
                        <button class="btn-seleccionar"
                                onclick='llenarFormularioEdicion(<?= json_encode($d); ?>)'>
                            Seleccionar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        /**
         * Función JavaScript para mostrar u ocultar las materias de un semestre específico en el formulario de edición.
         * Se llama cuando se marca/desmarca un checkbox de semestre.
         * @param {number} semestre - El número del semestre cuyas materias se deben mostrar/ocultar.
         */
        function toggleMateriasPorSemestreEdicion(semestre) {
            const checkbox = document.getElementById(`edit_semestre_${semestre}`);
            const materias = document.querySelectorAll(`.materia-semestre-${semestre}`);
            
            materias.forEach(materia => {
                if (checkbox.checked) {
                    materia.style.display = 'flex'; // Muestra la materia como flexbox.
                } else {
                    materia.style.display = 'none'; // Oculta la materia.
                    materia.querySelector('input[type="checkbox"]').checked = false; // Desmarca el checkbox de la materia.
                    materia.querySelector('select').value = ''; // Resetea la selección del grupo.
                }
            });
        }

        /**
         * Función JavaScript para llenar el formulario de edición con los datos de un docente seleccionado.
         * @param {Object} docente - Objeto JSON con los datos del docente.
         */
        function llenarFormularioEdicion(docente) {
            // Asigna los valores del docente a los campos del formulario.
            document.getElementById("edit_id_usuario").value = docente.id_usuario;
            document.getElementById("edit_nombre").value = docente.nombre;
            document.getElementById("edit_apellido_pat").value = docente.apellido_pat;
            document.getElementById("edit_apellido_mat").value = docente.apellido_mat;
            document.getElementById("edit_correo").value = docente.correo;
            
            const rolSelect = document.getElementById("edit_rol");
            // Convierte la cadena de IDs de roles a un array de enteros.
            const idsRolesAsignados = docente.ids_roles_asignados.split(',').map(id => parseInt(id.trim()));

            // Obtener el div del mensaje de tutoría.
            const mensajeTutorMaterias = document.getElementById("mensaje_tutor_materias");
            
            let foundRolOption = false;
            // Si el docente tiene tanto el rol de Profesor como el de Tutor.
            if (idsRolesAsignados.includes(1) && idsRolesAsignados.includes(2)) {
                for (let i = 0; i < rolSelect.options.length; i++) {
                    // Busca la opción "1,2" (Profesor/Tutor) y la selecciona.
                    if (rolSelect.options[i].value === '1,2') {
                        rolSelect.value = '1,2';
                        foundRolOption = true;
                        break;
                    }
                }
                // Si el docente tiene ambos roles, muestra el mensaje de tutoría.
                mensajeTutorMaterias.style.display = 'block';
            } else if (idsRolesAsignados.includes(1)) {
                // Si solo tiene el rol de Profesor.
                rolSelect.value = '1';
                foundRolOption = true;
                mensajeTutorMaterias.style.display = 'none'; // Oculta el mensaje.
            } else if (idsRolesAsignados.includes(2)) {
                // Si solo tiene el rol de Tutor (aunque en este contexto es menos común por sí solo).
                rolSelect.value = '2';
                foundRolOption = true;
                mensajeTutorMaterias.style.display = 'none'; // Oculta el mensaje.
            }

            // Si no se encontró ninguna opción de rol que coincida, resetea el select y oculta el mensaje.
            if (!foundRolOption) {
                rolSelect.value = '';
                mensajeTutorMaterias.style.display = 'none';
            }

            // Reinicia todas las selecciones de semestre y materias.
            document.querySelectorAll('.semestres-container input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.materias-container .materia-item').forEach(materiaItem => {
                materiaItem.style.display = 'none'; // Oculta todas las materias inicialmente.
                materiaItem.querySelector('input[type="checkbox"]').checked = false; // Desmarca su checkbox.
                materiaItem.querySelector('select').value = ''; // Resetea su select de grupo.
            });

            // Si el docente tiene materias asignadas, procesa y selecciona en el formulario.
            if (docente.materias_asignadas_json) {
                const materiasAsignadas = JSON.parse(docente.materias_asignadas_json);
                const semestresYaMostrados = new Set(); // Para controlar qué semestres ya se procesaron.
                const groupedMateriasByAssignment = {}; // Objeto para agrupar las asignaciones por ID de materia.

                // Primera pasada: Agrupa las asignaciones por ID de materia y recoge los grupos.
                materiasAsignadas.forEach(ma => {
                    if (!groupedMateriasByAssignment[ma.id_materia]) {
                        groupedMateriasByAssignment[ma.id_materia] = {
                            semestre: ma.semestre, // Guarda el semestre para marcar el checkbox principal.
                            grupos: new Set() // Usa un Set para evitar grupos duplicados.
                        };
                    }
                    groupedMateriasByAssignment[ma.id_materia].grupos.add(ma.id_grupo);
                });

                // Segunda pasada: Actualiza el formulario basándose en las asignaciones agrupadas.
                for (const materiaId in groupedMateriasByAssignment) {
                    const materiaData = groupedMateriasByAssignment[materiaId];
                    // Convierte el Set de grupos a un Array y lo ordena numéricamente.
                    const assignedGroups = Array.from(materiaData.grupos).sort((a, b) => a - b);

                    // Marca el checkbox del semestre correspondiente.
                    const semestreCheckbox = document.getElementById(`edit_semestre_${materiaData.semestre}`);
                    if (semestreCheckbox) {
                        semestreCheckbox.checked = true;
                        // Llama a toggleMateriasPorSemestreEdicion solo una vez por semestre para mostrar todas sus materias.
                        if (!semestresYaMostrados.has(materiaData.semestre)) {
                            toggleMateriasPorSemestreEdicion(materiaData.semestre);
                            semestresYaMostrados.add(materiaData.semestre);
                        }
                    }

                    // Activa el checkbox de la materia y establece el grupo.
                    const materiaItem = document.getElementById(`edit_materia_${materiaId}`);
                    if (materiaItem) {
                        const materiaCheckbox = materiaItem.querySelector('input[type="checkbox"]');
                        const grupoSelect = materiaItem.querySelector('select');

                        materiaCheckbox.checked = true;
                        materiaItem.style.display = 'flex'; // Asegura que la materia sea visible.

                        // Selecciona la opción correcta en el select de grupo.
                        if (assignedGroups.length === 2 && assignedGroups[0] === 1 && assignedGroups[1] === 2) {
                            grupoSelect.value = '1,2'; // Selecciona la opción C/D.
                        } else if (assignedGroups.length === 1) {
                            grupoSelect.value = assignedGroups[0].toString(); // Selecciona un solo grupo.
                        } else {
                            grupoSelect.value = ''; // Si la combinación es inesperada, no selecciona nada.
                        }
                    }
                }
            }

            // Desplaza la vista hacia el formulario de edición.
            document.querySelector('.form-edicion').scrollIntoView({
                behavior: 'smooth'
            });
        }

        /**
         * Función JavaScript para manejar la visibilidad del mensaje de tutoría
         * cuando se cambia la selección del rol en el formulario de edición.
         */
        function manejarCambioRol() {
            const rolSelect = document.getElementById("edit_rol");
            const mensajeTutorMaterias = document.getElementById("mensaje_tutor_materias");

            if (rolSelect.value === '1,2') { // Si se selecciona 'Profesor/Tutor'
                mensajeTutorMaterias.style.display = 'block'; // Muestra el mensaje.
            } else {
                mensajeTutorMaterias.style.display = 'none'; // Oculta el mensaje.
            }
        }

        // Validación del formulario de edición al intentar enviarlo.
        document.getElementById('form_editar_docente').addEventListener('submit', function(e) {
            const errorMessages = []; // Array para almacenar los mensajes de error.
            
            // Obtener referencias a los campos del formulario.
            const id_usuario = this.querySelector('input[name="id_usuario"]');
            const nombre = this.querySelector('input[name="nombre"]');
            const apellido_pat = this.querySelector('input[name="apellido_pat"]');
            const apellido_mat = this.querySelector('input[name="apellido_mat"]');
            const correo = this.querySelector('input[name="correo"]');
            const rol = this.querySelector('select[name="rol_seleccionado"]');

            // Validaciones de campos obligatorios y formato.
            if (!id_usuario.value.trim()) { errorMessages.push('El ID de usuario es obligatorio (selecciona un docente de la tabla).'); }
            if (!nombre.value.trim()) { errorMessages.push('El campo Nombre es obligatorio.'); nombre.style.borderColor = 'red'; } else { nombre.style.borderColor = ''; }
            if (!apellido_pat.value.trim()) { errorMessages.push('El campo Apellido Paterno es obligatorio.'); apellido_pat.style.borderColor = 'red'; } else { apellido_pat.style.borderColor = ''; }
            if (!apellido_mat.value.trim()) { errorMessages.push('El campo Apellido Materno es obligatorio.'); apellido_mat.style.borderColor = 'red'; } else { apellido_mat.style.borderColor = ''; }
            if (!correo.value.trim()) { errorMessages.push('El campo Correo Electrónico es obligatorio.'); correo.style.borderColor = 'red'; } else { correo.style.borderColor = ''; }
            if (!rol.value) { errorMessages.push('Selecciona un rol.'); rol.style.borderColor = 'red'; } else { rol.style.borderColor = ''; }

            // Validar que al menos un semestre esté seleccionado.
            const semestresSeleccionados = this.querySelectorAll('input[name="semestres[]"]:checked').length;
            if (semestresSeleccionados === 0) {
                errorMessages.push('Selecciona al menos un semestre.');
            }

            let materiasValidas = 0; // Contador de materias con grupos válidos.
            let materiasSeleccionadasPeroSinGrupo = 0; // Contador de materias marcadas pero sin grupo seleccionado.

            // Itera sobre todas las materias marcadas en el formulario.
            this.querySelectorAll('input[name="materias[]"]:checked').forEach(checkbox => {
                const materiaItem = checkbox.closest('.materia-item');
                // Solo valida si la materia está visible (su checkbox de semestre está marcado).
                if (materiaItem && materiaItem.style.display !== 'none') {
                    const materiaId = checkbox.value;
                    const grupoSelect = this.querySelector(`select[name="grupos_${materiaId}"]`);
                    if (grupoSelect) {
                        // Verifica si el grupo seleccionado es '1,2' (C/D) o un ID de grupo válido (no 0 y no 4).
                        if (grupoSelect.value === '1,2') {
                            materiasValidas++;
                        } else if (grupoSelect.value && parseInt(grupoSelect.value) > 0 && parseInt(grupoSelect.value) !== 4) {
                            materiasValidas++;
                        } else if (parseInt(grupoSelect.value) === 4) {
                            // Si el grupo es 4 (asumido como no válido).
                            errorMessages.push(`El grupo seleccionado para la materia "${materiaItem.querySelector('label').innerText}" no es válido.`);
                        } else {
                            // Si la materia está marcada pero no se ha seleccionado un grupo válido.
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

            // Si hay errores, previene el envío del formulario y muestra una alerta.
            if (errorMessages.length > 0) {
                e.preventDefault(); // Detiene el envío del formulario.
                alert('⚠ ' + errorMessages.join('\n⚠ ')); // Muestra una alerta con todos los errores.
            }
        });
    </script>

    <?php
    return ob_get_clean(); // Devuelve el contenido del búfer de salida.
}
// Registra el shortcode 'formulario_editar_docente' con la función correspondiente.
add_shortcode('formulario_editar_docente', 'formulario_editar_docente_shortcode');

/**
 * Función para procesar la actualización de los datos de un docente.
 * Se ejecuta cuando se detecta el envío del formulario de edición por el método POST.
 */
function procesar_actualizacion_docente() {
    if (isset($_POST['actualizar_docente'])) {
        global $wpdb; // Accede a la instancia global de $wpdb.

        // Sanitizar y obtener los datos del formulario.
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $nombre = sanitize_text_field($_POST['nombre'] ?? '');
        $ap_pat = sanitize_text_field($_POST['apellido_pat'] ?? '');
        $ap_mat = sanitize_text_field($_POST['apellido_mat'] ?? '');
        $correo = sanitize_email($_POST['correo'] ?? '');
        $rol_seleccionado = sanitize_text_field($_POST['rol_seleccionado'] ?? ''); // Valor del select del rol (puede ser "1" o "1,2")
        $semestres = isset($_POST['semestres']) ? array_map('intval', $_POST['semestres']) : []; // Semestres seleccionados.
        $materias_post = $_POST['materias'] ?? []; // IDs de las materias seleccionadas.

        // Validaciones básicas del lado del servidor.
        if ($id_usuario === 0 || empty($nombre) || empty($ap_pat) || empty($ap_mat) || empty($correo) || empty($rol_seleccionado) || empty($semestres)) {
            error_log('Validation Error (Server-side - update): Faltan campos obligatorios o ID de usuario inválido.');
            wp_redirect(add_query_arg('msg', 'error')); // Redirige con un mensaje de error genérico.
            exit;
        }

        // Iniciar una transacción de base de datos para asegurar la atomicidad de las operaciones.
        $wpdb->query('START TRANSACTION');

        try {
            // Datos a actualizar en la tabla 'usuario'.
            $data_to_update = [
                'nombre' => $nombre,
                'apellido_mat' => $ap_mat,
                'apellido_pat' => $ap_pat,
                'correo' => $correo,
            ];
            
            // Verificar si el nuevo correo ya existe en otro usuario (excluyendo al usuario actual).
            $existing_user_with_email = $wpdb->get_var($wpdb->prepare(
                "SELECT id_usuario FROM usuario WHERE correo = %s AND id_usuario != %d",
                $correo, $id_usuario
            ));
            if ($existing_user_with_email) {
                throw new Exception('El correo electrónico ya está registrado por otro usuario.');
            }

            // Actualizar los datos del usuario en la tabla 'usuario'.
            $updated_user = $wpdb->update('usuario', $data_to_update, ['id_usuario' => $id_usuario]);

            // Si la actualización del usuario falló, lanza una excepción.
            if ($updated_user === false) {
                throw new Exception('Error al actualizar datos del usuario: ' . $wpdb->last_error);
            }

            // --- Actualizar roles ---
            // Eliminar los roles de Profesor y Tutor para este usuario, pero no el de 'Jefe de Carrera' si lo tuviera.
            $wpdb->query($wpdb->prepare(
                "DELETE ur FROM usuariorol ur JOIN rol r ON ur.id_rol = r.id_rol WHERE ur.id_usuario = %d AND r.nombre NOT IN ('Jefe de Carrera')",
                $id_usuario
            ));

            // Insertar los nuevos roles seleccionados.
            $roles_a_insertar = explode(',', $rol_seleccionado);
            foreach ($roles_a_insertar as $id_rol) {
                $id_rol = intval($id_rol);
                // Asegurarse de no reinsertar el rol de Jefe de Carrera (ID 3) si ya existía y no fue seleccionado explícitamente.
                if ($id_rol !== 3) {
                    $wpdb->insert('usuariorol', [
                        'id_usuario' => $id_usuario,
                        'id_rol' => $id_rol
                    ]);
                }
            }

            // --- Validación de asignaciones existentes antes de eliminarlas y reasignar ---
            // Este bloque previene la asignación de una materia/grupo que ya está asignada a OTRA docente.
            foreach ($materias_post as $id_materia) {
                $id_materia = intval($id_materia);
                $grupo_valor_post = $_POST["grupos_" . $id_materia] ?? ''; // Obtiene el valor del grupo (ej., '1', '2', '1,2').

                $grupos_para_esta_materia = [];
                // Determina los IDs de grupo a partir del valor del POST.
                if ($grupo_valor_post === '1,2') { // Si se seleccionó "C/D"
                    $grupos_para_esta_materia = [1, 2]; // Asume IDs 1 y 2 para C y D.
                } elseif (is_numeric($grupo_valor_post) && intval($grupo_valor_post) > 0 && intval($grupo_valor_post) !== 4) {
                    // Si es un ID de grupo numérico válido y no es el grupo 4 (General).
                    $grupos_para_esta_materia = [intval($grupo_valor_post)];
                } else {
                    continue; // Saltar si el grupo no es válido o es el grupo 4.
                }

                // Obtener el semestre de la materia desde la base de datos.
                $semestre_materia = $wpdb->get_var($wpdb->prepare(
                    "SELECT semestre FROM materia WHERE id_materia = %d",
                    $id_materia
                ));

                if ($semestre_materia === null) {
                    error_log("Se intentó procesar una materia inexistente (ID: $id_materia) durante la actualización.");
                    continue;
                }

                // Para cada grupo asociado a la materia, verificar si ya está asignado a otro docente.
                foreach ($grupos_para_esta_materia as $current_id_grupo) {
                    $query = $wpdb->prepare(
                        "SELECT COUNT(*) FROM asignacionusuariomateria
                         WHERE id_materia = %d AND id_grupo = %d AND semestre = %d AND id_usuario != %d",
                        $id_materia, $current_id_grupo, $semestre_materia, $id_usuario
                    );
                    $existe_asignacion_otro_docente = $wpdb->get_var($query);

                    if ($existe_asignacion_otro_docente > 0) {
                        // Si la combinación materia-grupo-semestre ya está asignada a otro docente, lanza una excepción.
                        $nombre_materia = $wpdb->get_var($wpdb->prepare("SELECT nombre FROM materia WHERE id_materia = %d", $id_materia));
                        $nombre_grupo = $wpdb->get_var($wpdb->prepare("SELECT nombre FROM grupo WHERE id_grupo = %d", $current_id_grupo));
                        throw new Exception("La materia '{$nombre_materia}' del semestre '{$semestre_materia}°' con grupo '{$nombre_grupo}' ya está asignada a otro docente.");
                    }
                }
            }

            // Si todas las validaciones de existencia pasaron, se procede a eliminar las antiguas asignaciones y agregar las nuevas.
            // Eliminar todas las asignaciones de materia-grupo existentes para este usuario.
            $wpdb->delete('asignacionusuariomateria', ['id_usuario' => $id_usuario]);

            $materias_asignadas_count = 0; // Contador de materias que se logran asignar.
            $any_materia_checkbox_checked = false; // Bandera para saber si se marcó al menos una materia.

            // Iterar sobre las materias que el usuario seleccionó en el formulario.
            foreach ($materias_post as $id_materia) {
                $any_materia_checkbox_checked = true; // Se marcó al menos un checkbox de materia.
                $id_materia = intval($id_materia);
                $grupo_valor_post = $_POST["grupos_" . $id_materia] ?? ''; // Valor del select del grupo.
                
                $grupos_a_asignar = [];

                if ($grupo_valor_post === '1,2') { // Si se seleccionó C/D.
                    $grupos_a_asignar = [1, 2]; // Se asignan a ambos grupos (1 y 2).
                } elseif (is_numeric($grupo_valor_post) && intval($grupo_valor_post) > 0 && intval($grupo_valor_post) !== 4) {
                    // Si es un ID de grupo válido y no es el grupo 4.
                    $grupos_a_asignar = [intval($grupo_valor_post)];
                } else {
                    // Si el grupo es inválido o el ID 4, se salta esta materia.
                    error_log("Grupo no válido o prohibido ($grupo_valor_post) para la materia $id_materia durante la actualización.");
                    continue;
                }

                // Obtener el semestre de la materia.
                $semestre = $wpdb->get_var($wpdb->prepare(
                    "SELECT semestre FROM materia WHERE id_materia = %d",
                    $id_materia
                ));
                
                if ($semestre === null) {
                    error_log("Se intentó asignar una materia inexistente (ID: $id_materia) durante la actualización.");
                    continue;
                }

                // Insertar cada asignación de materia-grupo-semestre para el usuario.
                foreach ($grupos_a_asignar as $current_id_grupo) {
                    $insert_asignacion = $wpdb->insert('asignacionusuariomateria', [
                        'id_usuario' => $id_usuario,
                        'id_materia' => $id_materia,
                        'id_grupo' => $current_id_grupo,
                        'semestre' => $semestre
                    ]);

                    if ($insert_asignacion === false) {
                        // Si falla la inserción de una asignación, lanza una excepción.
                        throw new Exception('Error al asignar materia y grupo durante la actualización: ' . $wpdb->last_error . ' (Materia: ' . $id_materia . ', Grupo: ' . $current_id_grupo . ')');
                    } else {
                        $materias_asignadas_count++; // Incrementa el contador de asignaciones exitosas.
                    }
                }
            }

            // Si se seleccionaron semestres pero ninguna materia con grupo válido fue asignada.
            if (!empty($semestres) && $materias_asignadas_count === 0 && $any_materia_checkbox_checked) {
                 throw new Exception('Se seleccionaron semestres pero no se asignaron materias válidas o los grupos seleccionados no son permitidos durante la actualización.');
            }

            // Si todas las operaciones fueron exitosas, confirma la transacción.
            $wpdb->query('COMMIT');
            wp_redirect(add_query_arg('msg', 'ok')); // Redirige con un mensaje de éxito.
            exit;

        } catch (Exception $e) {
            // En caso de cualquier error, revierte la transacción.
            $wpdb->query('ROLLBACK');
            error_log('Error en actualización de docente: ' . $e->getMessage()); // Registra el error.
            // Redirige con un mensaje de error específico si es por duplicidad de materia-grupo.
            if (strpos($e->getMessage(), 'ya está asignada a otro docente') !== false) {
                wp_redirect(add_query_arg('msg', 'materia_duplicada'));
            } else {
                wp_redirect(add_query_arg('msg', 'error'));
            }
            exit;
        }
    }
}
// Engancha la función `procesar_actualizacion_docente` al hook 'init' de WordPress.
add_action('init', 'procesar_actualizacion_docente');