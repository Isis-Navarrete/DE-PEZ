<?php
/**
 * Plugin Name: Registro de Docentes DEPEZ
 * Description: Formulario para registrar docentes y asignarles materias por semestre y grupo.
 * Version: 1.1
 * Author: Brian Guadalupe Fernández
 */

///////////////////////////////////////////////////////
// SHORTCODE: Mostrar formulario de registro docente //
///////////////////////////////////////////////////////
function formulario_registro_docente_shortcode() {
    global $wpdb;

    // Obtener los roles "Profesor" y "Tutor"
    $rol_profesor_obj = $wpdb->get_row("SELECT id_rol, nombre FROM rol WHERE nombre = 'Profesor'"); 
    $rol_tutor_obj = $wpdb->get_row("SELECT id_rol, nombre FROM rol WHERE nombre = 'Tutor'"); 

    $id_profesor = $rol_profesor_obj ? $rol_profesor_obj->id_rol : null;
    $id_tutor = $rol_tutor_obj ? $rol_tutor_obj->id_rol : null;

    // Preparar opciones del select de roles
    $roles_para_mostrar = [];
    if ($id_profesor !== null) {
        $roles_para_mostrar[] = ['id' => $id_profesor, 'nombre' => 'Profesor'];
    }
    if ($id_profesor !== null && $id_tutor !== null) {
        $roles_para_mostrar[] = ['id' => "$id_profesor,$id_tutor", 'nombre' => 'Profesor/Tutor'];
    }

    // Obtener todos los grupos (excepto el grupo con ID 4)
    $grupos = $wpdb->get_results("SELECT id_grupo, nombre FROM grupo WHERE id_grupo NOT IN (4) ORDER BY nombre");

    // Obtener todas las materias agrupadas por semestre
    $materias = $wpdb->get_results("SELECT id_materia, nombre, semestre FROM materia ORDER BY semestre, nombre");

    $materias_por_semestre = [];
    foreach ($materias as $m) {
        $materias_por_semestre[$m->semestre][] = $m;
    }

    ob_start();
?>

<!-- ============================ FORMULARIO HTML ============================ -->
<!-- Estilos CSS para diseño y validaciones visuales -->
<style>
    /* estilos omitidos por brevedad (ya están explicados en el código original) */
</style>

<!-- Mostrar mensajes según resultado del registro -->
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
    <p class="mensaje-exito">✅ Docente registrado correctamente.</p>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
    <p class="mensaje-error">❌ Error al registrar el docente. Verifica todos los campos.</p>
<?php endif; ?>

<!-- Formulario de registro -->
<form class="form-registro" method="post" id="form_docente">
    <!-- Datos personales -->
    <input type="text" name="nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
    <input type="text" name="apellido_pat" placeholder="Apellido Paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
    <input type="text" name="apellido_mat" placeholder="Apellido Materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
    <input type="email" name="correo" placeholder="Correo Electrónico" required>
    <input type="password" name="contrasena" placeholder="Contraseña" required minlength="6">

    <!-- Selección de rol -->
    <label for="tipo_docente">Rol:</label>
    <select name="tipo_docente" id="tipo_docente" required>
        <option value="">Seleccionar rol</option>
        <?php foreach ($roles_para_mostrar as $r): ?>
            <option value="<?= esc_attr($r['id']) ?>"><?= esc_html($r['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Semestres -->
    <label>Semestres:</label>
    <div class="semestres-container">
        <?php foreach ($materias_por_semestre as $semestre => $materias_semestre_actual): ?>
            <div class="semestre-item">
                <input type="checkbox" id="semestre_<?= $semestre ?>" onchange="toggleMaterias(<?= $semestre ?>)" name="semestres[]" value="<?= $semestre ?>">
                <label for="semestre_<?= $semestre ?>"><?= $semestre ?>° Semestre</label>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Materias con opción de grupo por cada materia -->
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
                        <option value="1,2">C/D</option>
                    </select>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Botones -->
    <div class="form-buttons">
        <button type="submit" name="registrar_docente">Guardar</button>
        <a href="https://nonstop-taniz.mnz.dom.my.id/?page_id=65" class="button-regresar">Regresar</a>
    </div>
</form>

<!-- ============================ SCRIPT JS ============================ -->
<script>
    // Mostrar u ocultar materias según semestre seleccionado
    function toggleMaterias(semestre) {
        const checkbox = document.getElementById(`semestre_${semestre}`);
        const materias = document.querySelectorAll(`.materia-item[data-semestre="${semestre}"]`);
        materias.forEach(materia => {
            materia.style.display = checkbox.checked ? 'flex' : 'none';
            if (!checkbox.checked) {
                materia.querySelector('input[type="checkbox"]').checked = false;
                materia.querySelector('select').value = '';
            }
        });
    }

    // Validación del formulario
    document.getElementById('form_docente').addEventListener('submit', function(e) {
        // Validaciones específicas (campos obligatorios, contraseña mínima, selección de materias con grupo, etc.)
        // Código omitido por espacio (ya está validado en JS original)
    });
</script>

<?php
    return ob_get_clean(); // Devolver contenido del formulario
}
add_shortcode('formulario_registro_docente', 'formulario_registro_docente_shortcode');

//////////////////////////////////////////////////////
// BACKEND: Procesar registro y asignaciones docente
//////////////////////////////////////////////////////
function procesar_registro_docente() {
    if (isset($_POST['registrar_docente'])) {
        global $wpdb;

        // Sanitizar entradas del formulario
        $nombre = sanitize_text_field($_POST['nombre'] ?? '');
        $ap_pat = sanitize_text_field($_POST['apellido_pat'] ?? '');
        $ap_mat = sanitize_text_field($_POST['apellido_mat'] ?? '');
        $correo = sanitize_email($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $rol_seleccionado_value = $_POST['tipo_docente'] ?? '';
        $semestres = isset($_POST['semestres']) ? array_map('intval', $_POST['semestres']) : [];

        // Validar campos básicos
        if (empty($nombre) || empty($ap_pat) || empty($ap_mat) || empty($correo) ||
            strlen($contrasena) < 6 || empty($rol_seleccionado_value) || empty($semestres)) {
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }

        // Iniciar transacción para seguridad de datos
        $wpdb->query('START TRANSACTION');

        try {
            // Verificar que el correo no esté duplicado
            $existing_user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id_usuario FROM usuario WHERE correo = %s",
                $correo
            ));
            if ($existing_user_id) throw new Exception('Correo ya registrado');

            // Insertar nuevo docente
            $wpdb->insert('usuario', [
                'nombre' => $nombre,
                'apellido_mat' => $ap_mat,
                'apellido_pat' => $ap_pat,
                'correo' => $correo,
                'contrasena' => $contrasena,
                'telefono' => ''
            ]);
            $id_usuario = $wpdb->insert_id;

            // Asignar roles (puede ser uno o dos separados por coma)
            $roles_a_insertar = explode(',', $rol_seleccionado_value);
            foreach ($roles_a_insertar as $id_rol) {
                $id_rol = intval($id_rol);
                if ($id_rol === 1 || $id_rol === 2) {
                    $wpdb->insert('usuariorol', ['id_usuario' => $id_usuario, 'id_rol' => $id_rol]);
                }
            }

            // Asignación de materias y grupos
            foreach ($_POST['materias'] ?? [] as $id_materia) {
                $id_materia = intval($id_materia);
                $grupo_seleccionado_value = $_POST["grupos_$id_materia"] ?? '';

                $semestre = $wpdb->get_var($wpdb->prepare(
                    "SELECT semestre FROM materia WHERE id_materia = %d", $id_materia
                ));

                $grupos_a_insertar = [];
                if ($grupo_seleccionado_value === '1,2') {
                    $grupos_a_insertar = [1, 2];
                } elseif (intval($grupo_seleccionado_value) > 0 && intval($grupo_seleccionado_value) !== 4) {
                    $grupos_a_insertar[] = intval($grupo_seleccionado_value);
                }

                foreach ($grupos_a_insertar as $id_grupo) {
                    $wpdb->insert('asignacionusuariomateria', [
                        'id_usuario' => $id_usuario,
                        'id_materia' => $id_materia,
                        'id_grupo' => $id_grupo,
                        'semestre' => $semestre
                    ]);
                }
            }

            // Confirmar transacción
            $wpdb->query('COMMIT');
            wp_redirect(add_query_arg('msg', 'ok'));
            exit;

        } catch (Exception $e) {
            // Revertir en caso de error
            $wpdb->query('ROLLBACK');
            wp_redirect(add_query_arg('msg', 'error'));
            exit;
        }
    }
}
add_action('init', 'procesar_registro_docente');