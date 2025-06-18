<?php
/**
 * Plugin Name: Editar Docente DEPEZ
 * Description: Permite seleccionar y editar docentes, incluyendo roles, materias y grupos.
 * Version: 1.0
 * Author: Brian Guadalupe Fernández
 */

////////////////////////////////////////////////////////////////
// SHORTCODE: Mostrar formulario de edición y tabla de docentes //
////////////////////////////////////////////////////////////////
/**
 * Genera el HTML para el formulario de edición de docentes y una tabla para seleccionarlos.
 * Este shortcode debe ser insertado en una página de WordPress para funcionar.
 *
 * @global wpdb $wpdb Objeto global de la base de datos de WordPress.
 * @return string El contenido HTML del formulario y la tabla.
 */
function formulario_editar_docente_shortcode() {
    global $wpdb;

    // Obtener los IDs y nombres de los roles "Profesor" (ID 1) y "Tutor" (ID 2)
    // Se excluyen otros roles como "Jefe de Carrera" que no deben ser asignados desde aquí.
    $roles_db = $wpdb->get_results("SELECT id_rol, nombre FROM rol WHERE id_rol IN (1, 2)");
    $id_profesor = null;
    $id_tutor = null;

    // Asignar los IDs de rol a variables para facilitar su uso
    foreach ($roles_db as $r) {
        if ($r->nombre === 'Profesor') {
            $id_profesor = $r->id_rol;
        } elseif ($r->nombre === 'Tutor') {
            $id_tutor = $r->id_rol;
        }
    }

    // Preparar las opciones de rol para el elemento <select> del formulario.
    // Incluye la opción de "Profesor/Tutor" si ambos roles existen.
    $roles_para_mostrar = [];
    if ($id_profesor !== null) {
        $roles_para_mostrar[] = ['id' => $id_profesor, 'nombre' => 'Profesor'];
    }
    if ($id_profesor !== null && $id_tutor !== null) {
        // El valor para la combinación Profesor/Tutor es una cadena con ambos IDs separados por coma.
        $roles_para_mostrar[] = ['id' => "$id_profesor,$id_tutor", 'nombre' => 'Profesor/Tutor'];
    }

    // Obtener todos los grupos disponibles de la base de datos, excluyendo el grupo con ID 4 (General).
    $grupos = $wpdb->get_results("SELECT id_grupo, nombre FROM grupo WHERE id_grupo != 4 ORDER BY nombre");

    // Obtener todas las materias de la base de datos.
    $materias_todas = $wpdb->get_results("SELECT id_materia, nombre, semestre FROM materia ORDER BY semestre, nombre");
    
    // Agrupar las materias por semestre para facilitar la visualización en el formulario.
    $materias_por_semestre = [];
    foreach ($materias_todas as $m) {
        $materias_por_semestre[$m->semestre][] = $m;
    }

    ///////////////////////////////////////////////
    // CONSULTA PARA OBTENER DATOS DE LOS DOCENTES //
    ///////////////////////////////////////////////
    // Esta consulta obtiene la información de los docentes, sus roles asignados y
    // las materias con sus respectivos grupos que tienen asignadas.
    // Excluye a los usuarios que tienen el rol de 'Jefe de Carrera'.
    $docentes = $wpdb->get_results("
        SELECT
            u.id_usuario,
            u.nombre,
            u.apellido_pat,
            u.apellido_mat,
            u.correo,
            GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS roles,
            GROUP_CONCAT(DISTINCT ur.id_rol ORDER BY ur.id_rol SEPARATOR ',') AS ids_roles_asignados,
            (
                SELECT JSON_ARRAYAGG(
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
            WHERE r2.nombre = 'Jefe de Carrera' -- Aseguramos la mayúscula inicial
        )
        GROUP BY u.id_usuario, u.nombre, u.apellido_pat, u.apellido_mat, u.correo
        HAVING roles NOT LIKE '%Jefe de Carrera%' -- Aseguramos la mayúscula inicial
        ORDER BY u.id_usuario ASC, u.apellido_pat, u.apellido_mat, u.nombre
    ");

    // Iniciar el almacenamiento en búfer de salida para capturar el HTML
    ob_start();
    ?>

    <style>
        .form-edicion, .formulario-eliminar { 
            max-width: 700px;
            margin: 0 auto;
            background: #f3f3f3;
            padding: 20px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
            margin-bottom: 30px;
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
        .form-actions { /* Estilo para el contenedor de botones */
            display: flex;
            justify-content: space-between; /* Alinea los elementos a los extremos */
            align-items: center;
            margin-top: 20px;
        }
        .form-edicion button {
            background-color: #FF0000;
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
            text-decoration: none; /* Para que parezca un botón */
            display: inline-block; /* Para que respete el padding y margin */
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
            display: none; /* Oculto por defecto, se muestra con JS */
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

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
        <p class="mensaje-exito">✅ Docente actualizado correctamente.</p>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
        <p class="mensaje-error">❌ Error al actualizar el docente. Verifica todos los campos.</p>
    <?php endif; ?>

    <form class="form-edicion" method="post" id="form_editar_docente">
        <input type="hidden" name="id_usuario" id="edit_id_usuario" required>

        <input type="text" name="nombre" id="edit_nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_pat" id="edit_apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="text" name="apellido_mat" id="edit_apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
        <input type="email" name="correo" id="edit_correo" placeholder="Correo Electrónico" required>
        
        <label for="edit_rol">Rol:</label>
        <select name="rol_seleccionado" id="edit_rol" required>
            <option value="">Seleccionar rol</option>
            <?php foreach ($roles_para_mostrar as $r): ?>
                <option value="<?= esc_attr($r['id']) ?>"><?= esc_html($r['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

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
            <?php foreach ($docentes as $d):
                // Decodificar las materias asignadas desde JSON
                $materias_asignadas_data = json_decode($d->materias_asignadas_json, true);
                $materias_info = [];
                if (is_array($materias_asignadas_data)) {
                    // Formatear la información de las materias para mostrar en la tabla
                    foreach ($materias_asignadas_data as $ma) {
                        $materias_info[] = esc_html("{$ma['nombre_materia']} ({$ma['semestre']}° - {$ma['nombre_grupo']})");
                    }
                }
            ?>
                <tr>
                    <td><?= esc_html($d->id_usuario); ?></td>
                    <td><?= esc_html("{$d->nombre} {$d->apellido_pat} {$d->apellido_mat}"); ?></td>
                    <td><?= esc_html($d->correo); ?></td>
                    <td><?= esc_html($d->roles); ?></td>
                    <td><?= implode('<br>', $materias_info); ?></td>
                    <td>
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
         * Muestra/oculta las materias de un semestre específico según el estado de su checkbox.
         * Desmarca y limpia los selects de grupo si el semestre se desactiva.
         * @param {number} semestre El número del semestre.
         */
        function toggleMateriasPorSemestreEdicion(semestre) {
            const checkbox = document.getElementById(`edit_semestre_${semestre}`);
            const materias = document.querySelectorAll(`.materia-semestre-${semestre}`);
            
            materias.forEach(materia => {
                if (checkbox.checked) {
                    materia.style.display = 'flex'; // Mostrar las materias del semestre
                } else {
                    materia.style.display = 'none'; // Ocultar las materias
                    materia.querySelector('input[type="checkbox"]').checked = false; // Desmarcar materia
                    materia.querySelector('select').value = ''; // Limpiar select de grupo
                }
            });
        }

        /**
         * Llena el formulario de edición con los datos del docente seleccionado de la tabla.
         * Activa los semestres y materias/grupos correspondientes.
         * @param {object} docente Un objeto JSON con los datos del docente.
         */
        function llenarFormularioEdicion(docente) {
            document.getElementById("edit_id_usuario").value = docente.id_usuario;
            document.getElementById("edit_nombre").value = docente.nombre;
            document.getElementById("edit_apellido_pat").value = docente.apellido_pat;
            document.getElementById("edit_apellido_mat").value = docente.apellido_mat;
            document.getElementById("edit_correo").value = docente.correo;
            
            const rolSelect = document.getElementById("edit_rol");
            // Convertir la cadena de IDs de roles a un array de enteros
            const idsRolesAsignados = docente.ids_roles_asignados.split(',').map(id => parseInt(id.trim()));
            
            let foundRolOption = false;
            // Si el docente tiene ambos roles (Profesor y Tutor)
            if (idsRolesAsignados.includes(1) && idsRolesAsignados.includes(2)) {
                for (let i = 0; i < rolSelect.options.length; i++) {
                    if (rolSelect.options[i].value === '1,2') {
                        rolSelect.value = '1,2'; // Seleccionar la opción "Profesor/Tutor"
                        foundRolOption = true;
                        break;
                    }
                }
            } else if (idsRolesAsignados.includes(1)) {
                rolSelect.value = '1'; // Seleccionar "Profesor"
                foundRolOption = true;
            } else if (idsRolesAsignados.includes(2)) {
                rolSelect.value = '2'; // Seleccionar "Tutor"
                foundRolOption = true;
            }

            // Si no se encontró una opción de rol adecuada, dejar el select sin seleccionar
            if (!foundRolOption) {
                rolSelect.value = '';
            }

            // Restablecer todas las selecciones de semestres y materias/grupos
            document.querySelectorAll('.semestres-container input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.materias-container .materia-item').forEach(materiaItem => {
                materiaItem.style.display = 'none'; // Ocultar todas las materias inicialmente
                materiaItem.querySelector('input[type="checkbox"]').checked = false;
                materiaItem.querySelector('select').value = '';
            });

            // Si el docente tiene materias asignadas, procesarlas y actualizar el formulario
            if (docente.materias_asignadas_json) {
                const materiasAsignadas = JSON.parse(docente.materias_asignadas_json);
                const semestresYaMostrados = new Set();
                const groupedMateriasByAssignment = {}; // Objeto para agrupar asignaciones por ID de materia

                // Primera pasada: Agrupar las asignaciones por ID de materia y recolectar los grupos
                materiasAsignadas.forEach(ma => {
                    if (!groupedMateriasByAssignment[ma.id_materia]) {
                        groupedMateriasByAssignment[ma.id_materia] = {
                            semestre: ma.semestre, // Almacenar el semestre para marcar el checkbox
                            grupos: new Set() // Usar un Set para evitar grupos duplicados
                        };
                    }
                    groupedMateriasByAssignment[ma.id_materia].grupos.add(ma.id_grupo);
                });

                // Segunda pasada: Actualizar el formulario basado en las asignaciones agrupadas
                for (const materiaId in groupedMateriasByAssignment) {
                    const materiaData = groupedMateriasByAssignment[materiaId];
                    // Convertir el Set de grupos a un Array y ordenar numéricamente
                    const assignedGroups = Array.from(materiaData.grupos).sort((a, b) => a - b);

                    // Marcar y activar el checkbox del semestre
                    const semestreCheckbox = document.getElementById(`edit_semestre_${materiaData.semestre}`);
                    if (semestreCheckbox) {
                        semestreCheckbox.checked = true;
                        // Llamar a toggleMateriasPorSemestreEdicion solo una vez por semestre
                        // para mostrar todas sus materias y evitar redundancia.
                        if (!semestresYaMostrados.has(materiaData.semestre)) {
                            toggleMateriasPorSemestreEdicion(materiaData.semestre);
                            semestresYaMostrados.add(materiaData.semestre);
                        }
                    }

                    // Marcar el checkbox de la materia y establecer el grupo correspondiente
                    const materiaItem = document.getElementById(`edit_materia_${materiaId}`);
                    if (materiaItem) {
                        const materiaCheckbox = materiaItem.querySelector('input[type="checkbox"]');
                        const grupoSelect = materiaItem.querySelector('select');

                        materiaCheckbox.checked = true;
                        materiaItem.style.display = 'flex'; // Asegurarse de que la materia sea visible

                        // Determinar el valor del select de grupo basado en los grupos asignados
                        if (assignedGroups.length === 2 && assignedGroups[0] === 1 && assignedGroups[1] === 2) {
                            grupoSelect.value = '1,2'; // Seleccionar la opción C/D
                        } else if (assignedGroups.length === 1) {
                            grupoSelect.value = assignedGroups[0].toString(); // Seleccionar un solo grupo
                        } else {
                            // Si la combinación de grupos es inesperada, dejar el select sin seleccionar
                            grupoSelect.value = ''; 
                        }
                    }
                }
            }

            // Desplazar la vista al formulario de edición
            document.querySelector('.form-edicion').scrollIntoView({
                behavior: 'smooth'
            });
        }

        /**
         * Validación del formulario de edición antes de enviar.
         * Muestra alertas con los errores encontrados.
         */
        document.getElementById('form_editar_docente').addEventListener('submit', function(e) {
            const errorMessages = [];
            
            // Obtener referencias a los campos del formulario
            const id_usuario = this.querySelector('input[name="id_usuario"]');
            const nombre = this.querySelector('input[name="nombre"]');
            const apellido_pat = this.querySelector('input[name="apellido_pat"]');
            const apellido_mat = this.querySelector('input[name="apellido_mat"]');
            const correo = this.querySelector('input[name="correo"]');
            const rol = this.querySelector('select[name="rol_seleccionado"]');

            // Validaciones de campos obligatorios
            if (!id_usuario.value.trim()) { errorMessages.push('El ID de usuario es obligatorio (selecciona un docente de la tabla).'); }
            if (!nombre.value.trim()) { errorMessages.push('El campo Nombre es obligatorio.'); nombre.style.borderColor = 'red'; } else { nombre.style.borderColor = ''; }
            if (!apellido_pat.value.trim()) { errorMessages.push('El campo Apellido Paterno es obligatorio.'); apellido_pat.style.borderColor = 'red'; } else { apellido_pat.style.borderColor = ''; }
            if (!apellido_mat.value.trim()) { errorMessages.push('El campo Apellido Materno es obligatorio.'); apellido_mat.style.borderColor = 'red'; } else { apellido_mat.style.borderColor = ''; }
            if (!correo.value.trim()) { errorMessages.push('El campo Correo Electrónico es obligatorio.'); correo.style.borderColor = 'red'; } else { correo.style.borderColor = ''; }
            if (!rol.value) { errorMessages.push('Selecciona un rol.'); rol.style.borderColor = 'red'; } else { rol.style.borderColor = ''; }

            // Validar selección de semestres
            const semestresSeleccionados = this.querySelectorAll('input[name="semestres[]"]:checked').length;
            if (semestresSeleccionados === 0) {
                errorMessages.push('Selecciona al menos un semestre.');
            }

            let materiasValidas = 0;
            let materiasSeleccionadasPeroSinGrupo = 0;

            // Validar materias y grupos asignados
            this.querySelectorAll('input[name="materias[]"]:checked').forEach(checkbox => {
                const materiaItem = checkbox.closest('.materia-item');
                // Asegurarse de que la materia esté actualmente visible (su semestre está marcado)
                if (materiaItem && materiaItem.style.display !== 'none') {
                    const materiaId = checkbox.value;
                    const grupoSelect = this.querySelector(`select[name="grupos_${materiaId}"]`);
                    if (grupoSelect) {
                        if (grupoSelect.value === '1,2') { // Manejar la opción C/D
                            materiasValidas++;
                        } else if (grupoSelect.value && parseInt(grupoSelect.value) > 0 && parseInt(grupoSelect.value) !== 4) {
                            materiasValidas++;
                        } else if (parseInt(grupoSelect.value) === 4) {
                            errorMessages.push(`El grupo seleccionado para la materia "${materiaItem.querySelector('label').innerText}" no es válido.`);
                        } else {
                            materiasSeleccionadasPeroSinGrupo++;
                        }
                    }
                }
            });

            // Mensajes de error específicos para materias y grupos
            if (materiasValidas === 0 && semestresSeleccionados > 0) {
                errorMessages.push('Selecciona al menos una materia con su grupo correspondiente.');
            }
            if (materiasSeleccionadasPeroSinGrupo > 0) {
                errorMessages.push('Asegúrate de seleccionar un grupo para cada materia marcada.');
            }

            // Si hay errores, prevenir el envío del formulario y mostrar un alert
            if (errorMessages.length > 0) {
                e.preventDefault();
                alert('⚠ ' + errorMessages.join('\n⚠ '));
            }
        });
    </script>

    <?php
    // Devolver el contenido del búfer de salida (el HTML generado)
    return ob_get_clean();
}
// Registrar el shortcode con WordPress
add_shortcode('formulario_editar_docente', 'formulario_editar_docente_shortcode');

//////////////////////////////////////////////////
// FUNCIÓN: Procesar la actualización del docente //
//////////////////////////////////////////////////
/**
 * Procesa los datos enviados desde el formulario de edición de docentes.
 * Actualiza la información del usuario, sus roles y las asignaciones de materias/grupos en la base de datos.
 * Esta función se ejecuta al inicio de WordPress (`init`).
 *
 * @global wpdb $wpdb Objeto global de la base de datos de WordPress.
 */
function procesar_actualizacion_docente() {
    // Verificar si el formulario de actualización fue enviado
    if (isset($_POST['actualizar_docente'])) {
        global $wpdb;

        // Sanitizar y obtener los datos del POST
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $nombre = sanitize_text_field($_POST['nombre'] ?? '');
        $ap_pat = sanitize_text_field($_POST['apellido_pat'] ?? '');
        $ap_mat = sanitize_text_field($_POST['apellido_mat'] ?? '');
        $correo = sanitize_email($_POST['correo'] ?? '');
        $rol_seleccionado = sanitize_text_field($_POST['rol_seleccionado'] ?? '');
        // Obtener los semestres seleccionados y sanitizarlos como enteros
        $semestres = isset($_POST['semestres']) ? array_map('intval', $_POST['semestres']) : [];

        // Validaciones básicas de los datos recibidos
        if ($id_usuario === 0 || empty($nombre) || empty($ap_pat) || empty($ap_mat) || empty($correo) || empty($rol_seleccionado) || empty($semestres)) {
            error_log('Validation Error (Server-side - update): Faltan campos obligatorios o ID de usuario inválido.');
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }

        // Iniciar una transacción para asegurar la atomicidad de las operaciones en la base de datos.
        $wpdb->query('START TRANSACTION');

        try {
            // Actualizar los datos personales del usuario en la tabla 'usuario'
            $data_to_update = [
                'nombre' => $nombre,
                'apellido_mat' => $ap_mat,
                'apellido_pat' => $ap_pat,
                'correo' => $correo,
            ];
            
            // Verificar si el nuevo correo electrónico ya está registrado por otro usuario
            $existing_user_with_email = $wpdb->get_var($wpdb->prepare(
                "SELECT id_usuario FROM usuario WHERE correo = %s AND id_usuario != %d",
                $correo, $id_usuario
            ));
            if ($existing_user_with_email) {
                throw new Exception('El correo electrónico ya está registrado por otro usuario.');
            }

            $updated_user = $wpdb->update('usuario', $data_to_update, ['id_usuario' => $id_usuario]);

            // Si la actualización del usuario falla, lanzar una excepción
            if ($updated_user === false) {
                throw new Exception('Error al actualizar datos del usuario: ' . $wpdb->last_error);
            }

            //////////////////////
            // ACTUALIZAR ROLES //
            //////////////////////
            // Eliminar solo los roles de "Profesor" y "Tutor" para este usuario.
            // Se asegura de no eliminar el rol de "Jefe de Carrera" si el usuario lo tuviera.
            $wpdb->query($wpdb->prepare(
                "DELETE ur FROM usuariorol ur JOIN rol r ON ur.id_rol = r.id_rol WHERE ur.id_usuario = %d AND r.nombre NOT IN ('Jefe de Carrera')",
                $id_usuario
            ));

            // Insertar los nuevos roles seleccionados para el usuario.
            $roles_a_insertar = explode(',', $rol_seleccionado);
            foreach ($roles_a_insertar as $id_rol) {
                $id_rol = intval($id_rol);
                // No reinsertar el rol de Jefe de Carrera si su ID es 3 y no se seleccionó explícitamente.
                if ($id_rol !== 3) { 
                    $wpdb->insert('usuariorol', [
                        'id_usuario' => $id_usuario,
                        'id_rol' => $id_rol
                    ]);
                }
            }

            /////////////////////////////////////////////
            // ACTUALIZAR ASIGNACIONES DE MATERIAS Y GRUPOS //
            /////////////////////////////////////////////
            // Eliminar todas las asignaciones de materias existentes para este usuario.
            // Esto permite reinsertar solo las selecciones actuales.
            $wpdb->delete('asignacionusuariomateria', ['id_usuario' => $id_usuario]);

            $materias_asignadas_count = 0; // Contador de materias válidamente asignadas
            $any_materia_checkbox_checked = false; // Bandera para saber si al menos un checkbox de materia fue marcado

            // Iterar sobre las materias recibidas del formulario
            foreach ($_POST['materias'] ?? [] as $id_materia) {
                $any_materia_checkbox_checked = true; // Se marcó al menos un checkbox de materia
                $id_materia = intval($id_materia);
                // Obtener el valor del grupo tal como viene del POST (puede ser '1,2' o un ID numérico)
                $grupo_valor_post = $_POST["grupos_" . $id_materia] ?? '';
                
                $grupos_a_asignar = [];

                // Determinar los grupos a asignar basado en el valor recibido
                if ($grupo_valor_post === '1,2') {
                    // Si se seleccionó la opción "C/D", asignar a los grupos 1 y 2
                    $grupos_a_asignar = [1, 2];
                } elseif (is_numeric($grupo_valor_post) && intval($grupo_valor_post) > 0 && intval($grupo_valor_post) !== 4) {
                    // Si es un ID de grupo numérico válido y no es el grupo 4 (General)
                    $grupos_a_asignar = [intval($grupo_valor_post)];
                } else {
                    // Si el grupo es inválido o el ID es 4, registrar un error y saltar a la siguiente materia
                    error_log("Grupo no válido o prohibido ($grupo_valor_post) para la materia $id_materia durante la actualización.");
                    continue; 
                }

                // Obtener el semestre de la materia desde la base de datos
                $semestre = $wpdb->get_var($wpdb->prepare(
                    "SELECT semestre FROM materia WHERE id_materia = %d", 
                    $id_materia
                ));
                
                // Si la materia no existe, registrar un error y saltar a la siguiente
                if ($semestre === null) {
                        error_log("Se intentó asignar una materia inexistente (ID: $id_materia) durante la actualización.");
                        continue;
                }

                // Insertar las asignaciones para cada grupo determinado
                foreach ($grupos_a_asignar as $current_id_grupo) {
                    $insert_asignacion = $wpdb->insert('asignacionusuariomateria', [
                        'id_usuario' => $id_usuario,
                        'id_materia' => $id_materia,
                        'id_grupo' => $current_id_grupo,
                        'semestre' => $semestre
                    ]);

                    // Si la inserción de la asignación falla, lanzar una excepción
                    if ($insert_asignacion === false) {
                        throw new Exception('Error al asignar materia y grupo durante la actualización: ' . $wpdb->last_error . ' (Materia: ' . $id_materia . ', Grupo: ' . $current_id_grupo . ')');
                    } else {
                        $materias_asignadas_count++; // Incrementar el contador de asignaciones exitosas
                    }
                }
            }

            // Validación final: si se seleccionaron semestres pero no se logró ninguna asignación válida
            if (!empty($semestres) && $materias_asignadas_count === 0 && $any_materia_checkbox_checked) { 
                 throw new Exception('Se seleccionaron semestres pero no se asignaron materias válidas o los grupos seleccionados no son permitidos durante la actualización.');
            }

            // Confirmar la transacción si todas las operaciones fueron exitosas
            $wpdb->query('COMMIT');
            // Redirigir con un mensaje de éxito
            wp_redirect(add_query_arg('msg', 'ok'));
            exit;

        } catch (Exception $e) {
            // Revertir la transacción en caso de cualquier error
            $wpdb->query('ROLLBACK');
            // Registrar el error en el log de WordPress
            error_log('Error en actualización de docente: ' . $e->getMessage());
            // Redirigir con un mensaje de error
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }
    }
}
// Adjuntar la función al hook 'init' de WordPress para que se ejecute al cargar la página
add_action('init', 'procesar_actualizacion_docente');