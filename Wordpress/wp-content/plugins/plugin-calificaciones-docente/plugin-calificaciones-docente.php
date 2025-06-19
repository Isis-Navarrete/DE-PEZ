<?php
/**
 * Plugin Name: Tabla de Calificaciones Docente DEPEZ
 * Description: Permite a los docentes subir y modificar calificaciones y faltas por unidad para los grupos donde imparten clases.
 * Version: 1.8
 * Author: Juan Taniz
 */

// Evita acceso directo
defined('ABSPATH') || exit;

// Inicia sesión si no existe
if (!session_id()) session_start();

// Define el shortcode principal
add_shortcode('tabla_calificaciones_docente', 'mostrar_tabla_calificaciones_docente');

function mostrar_tabla_calificaciones_docente() {
    global $wpdb;

    // Validación de sesión y parámetros GET
    if (!isset($_SESSION['usuario_id'], $_GET['grupo'], $_GET['semestre'])) {
        return '<p style="color:red;">Faltan parámetros requeridos: grupo y semestre.</p>';
    }

    // Validación de rol (1 = Docente)
    if (!in_array(1, $_SESSION['usuario_roles'] ?? [])) {
        return '<p style="color:red;">Acceso restringido. Solo para docentes.</p>';
    }

    // Sanitización de parámetros
    $grupo_nombre = sanitize_text_field($_GET['grupo']);
    $semestre = intval($_GET['semestre']);
    $usuario_id = intval($_SESSION['usuario_id']);

    // Obtener ID del grupo
    $grupo_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id_grupo FROM grupo WHERE nombre = %s", $grupo_nombre
    ));
    if (!$grupo_id) return '<p style="color:red;">Grupo no válido.</p>';

    // Obtener materias asignadas al docente para el grupo y semestre
    $materias = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT m.id_materia, m.nombre 
         FROM asignacionusuariomateria aum
         JOIN materia m ON aum.id_materia = m.id_materia
         WHERE aum.id_usuario = %d AND aum.id_grupo = %d AND aum.semestre = %d",
        $usuario_id, $grupo_id, $semestre
    ));
    if (!$materias) return '<p style="color:orange;">No tienes materias asignadas en este grupo.</p>';

    ob_start();

    // =====================
    // Formulario de selección de materia y unidad
    // =====================
    echo '<form method="post">';
    echo '<label for="materia_select">Selecciona una materia:</label>';
    echo '<select id="materia_select" name="id_materia" required>';
    foreach ($materias as $materia) {
        echo '<option value="' . esc_attr($materia->id_materia) . '">' . esc_html($materia->nombre) . '</option>';
    }
    echo '</select>';

    echo '<label for="unidad_limite">Hasta qué unidad se evaluará:</label>';
    echo '<select id="unidad_limite" name="unidad_limite" required>';
    for ($i = 1; $i <= 6; $i++) {
        echo "<option value='$i'>Unidad $i</option>";
    }
    echo '</select>';
    echo '<button type="submit" name="cargar_materia">Cargar</button>';
    echo '</form>';

    // =====================
    // Mostrar tabla si se ha seleccionado materia
    // =====================
    if (isset($_POST['cargar_materia'])) {
        $id_materia = intval($_POST['id_materia']);
        $unidad_limite = intval($_POST['unidad_limite']);

        // Obtener alumnos del grupo y materia
        $alumnos = $wpdb->get_results($wpdb->prepare(
            "SELECT a.id_alumno, a.nombre, a.apellido_pat, a.apellido_mat
             FROM alumno a
             JOIN asignacionmateria am ON a.id_alumno = am.id_alumno
             WHERE a.id_grupo = %d AND am.id_materia = %d",
            $grupo_id, $id_materia
        ));

        // Formulario con tabla de calificaciones y faltas
        echo '<form method="post">';
        echo '<input type="hidden" name="id_materia" value="' . esc_attr($id_materia) . '">';
        echo '<input type="hidden" name="unidad_limite" value="' . esc_attr($unidad_limite) . '">';
        echo '<table border="1" style="width:100%; text-align:center; font-family: Arial;">';
        echo '<thead><tr><th>Alumno</th>';
        for ($u = 1; $u <= $unidad_limite; $u++) {
            echo '<th>U' . $u . ' Cal</th><th>U' . $u . ' Faltas</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($alumnos as $a) {
            $nombre = esc_html("$a->nombre $a->apellido_pat $a->apellido_mat");
            echo '<tr><td>' . $nombre . '</td>';
            for ($u = 1; $u <= $unidad_limite; $u++) {
                $id_asigma = $wpdb->get_var($wpdb->prepare(
                    "SELECT id_asigma FROM asignacionmateria WHERE id_alumno = %d AND id_materia = %d",
                    $a->id_alumno, $id_materia
                ));
                $cal = $wpdb->get_var($wpdb->prepare(
                    "SELECT calificacion FROM UnidadMateria WHERE id_asigma = %d AND unidad = %d",
                    $id_asigma, $u
                ));
                $faltas = $wpdb->get_var($wpdb->prepare(
                    "SELECT faltas FROM UnidadMateria WHERE id_asigma = %d AND unidad = %d",
                    $id_asigma, $u
                ));
                echo '<td><input type="text" class="input-cal" inputmode="numeric" pattern="\d+" name="calificacion[' . $a->id_alumno . '][' . $u . ']" value="' . esc_attr((int)$cal) . '" maxlength="3"></td>';
                echo '<td><input type="text" class="input-faltas" inputmode="numeric" pattern="\d+" name="faltas[' . $a->id_alumno . '][' . $u . ']" value="' . esc_attr((int)$faltas) . '" maxlength="3"></td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<button type="submit" name="guardar_calificaciones">Guardar</button>';
        echo '</form>';

        // Formulario para volver a seleccionar la unidad
        echo '<form method="post">';
        echo '<input type="hidden" name="id_materia" value="' . esc_attr($id_materia) . '">';
        echo '<button type="submit" name="modificar_unidad">Modificar unidad evaluada</button>';
        echo '</form>';

        // Validación en tiempo real (JavaScript)
        echo '<script>
        document.querySelectorAll("input[type=text]").forEach((input, i, all) => {
            input.addEventListener("keydown", e => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const next = all[i + 1];
                    if (next) next.focus();
                }
            });
            input.addEventListener("input", e => {
                const val = parseInt(e.target.value);
                if (e.target.classList.contains("input-cal")) {
                    e.target.style.backgroundColor = (val > 100 || val < 0 || isNaN(val)) ? "#fbb" : "white";
                } else if (e.target.classList.contains("input-faltas")) {
                    e.target.style.backgroundColor = (val < 0 || isNaN(val)) ? "#fbb" : "white";
                }
            });
        });
        </script>';
    }

    // =====================
    // Guardar calificaciones y faltas
    // =====================
    if (isset($_POST['guardar_calificaciones'])) {
        $id_materia = intval($_POST['id_materia']);
        $unidad_limite = intval($_POST['unidad_limite']);
        $calificaciones = $_POST['calificacion'] ?? [];
        $faltas = $_POST['faltas'] ?? [];

        foreach ($calificaciones as $id_alumno => $por_unidad) {
            for ($unidad = 1; $unidad <= $unidad_limite; $unidad++) {
                $cal = isset($por_unidad[$unidad]) ? intval($por_unidad[$unidad]) : null;
                $falta = isset($faltas[$id_alumno][$unidad]) ? intval($faltas[$id_alumno][$unidad]) : null;
                if ($cal === null && $falta === null) continue;

                $id_asigma = $wpdb->get_var($wpdb->prepare(
                    "SELECT id_asigma FROM asignacionmateria WHERE id_alumno = %d AND id_materia = %d",
                    $id_alumno, $id_materia
                ));

                if ($id_asigma) {
                    // Eliminar registro previo
                    $wpdb->delete('UnidadMateria', [
                        'id_asigma' => $id_asigma,
                        'unidad' => $unidad
                    ]);

                    // Insertar nuevo
                    $wpdb->insert('UnidadMateria', [
                        'id_asigma' => $id_asigma,
                        'unidad' => $unidad,
                        'calificacion' => $cal,
                        'faltas' => $falta
                    ]);
                }
            }
        }

        echo '<p style="color:green; font-family:Arial;">✅ Calificaciones y faltas guardadas correctamente.</p>';
    }

    // =====================
    // Modificar unidad: reinicia flujo
    // =====================
    if (isset($_POST['modificar_unidad'])) {
        unset($_POST['cargar_materia']);
    }

    // =====================
    // Botón de regresar
    // =====================
    echo '<div style="margin-top: 30px; text-align: center;">
        <a href="javascript:history.back()" style="
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF0000;
            color: white;
            font-family: Arial, sans-serif;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;">
            Regresar
        </a>
    </div>';

    return ob_get_clean();
}

