<?php
/**
 * Plugin Name: Tabla de Calificaciones Docente DEPEZ
 * Description: Permite a los docentes subir y modificar calificaciones y faltas por unidad para los grupos donde imparten clases.
 * Version: 1.6
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;
if (!session_id()) session_start();

add_shortcode('tabla_calificaciones_docente', 'mostrar_tabla_calificaciones_docente');

function mostrar_tabla_calificaciones_docente() {
    global $wpdb;

    if (!isset($_SESSION['usuario_id']) || !isset($_GET['grupo']) || !isset($_GET['semestre'])) {
        return '<p>Faltan parámetros requeridos: grupo y semestre.</p>';
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $roles = $_SESSION['usuario_roles'] ?? [];
    if (!in_array(1, $roles)) {
        return '<p>Acceso restringido. Solo para docentes.</p>';
    }

    $grupo_nombre = sanitize_text_field($_GET['grupo']);
    $semestre = intval($_GET['semestre']);

    $grupo_id = $wpdb->get_var($wpdb->prepare("SELECT id_grupo FROM grupo WHERE nombre = %s", $grupo_nombre));
    if (!$grupo_id) return '<p>Grupo no válido.</p>';

    $materias = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT m.id_materia, m.nombre
         FROM asignacionusuariomateria aum
         JOIN materia m ON aum.id_materia = m.id_materia
         WHERE aum.id_usuario = %d AND aum.id_grupo = %d AND aum.semestre = %d",
        $usuario_id, $grupo_id, $semestre
    ));

    if (!$materias) return '<p>No tienes materias asignadas en este grupo.</p>';

    ob_start();
    echo '<form method="post">';
    echo '<label for="materia_select">Selecciona una materia:</label>';
    echo '<select id="materia_select" name="id_materia">';
    foreach ($materias as $materia) {
        echo '<option value="' . esc_attr($materia->id_materia) . '">' . esc_html($materia->nombre) . '</option>';
    }
    echo '</select><br><br>';

    // Unidad límite
    if (!isset($_POST['unidad_limite']) || isset($_POST['modificar_unidad'])) {
        echo '<label for="unidad_limite">Selecciona la unidad hasta la cual se evaluará:</label>';
        echo '<select id="unidad_limite" name="unidad_limite">';
        for ($u = 1; $u <= 6; $u++) {
            echo "<option value='$u'>Unidad $u</option>";
        }
        echo '</select><br><br>';
    } elseif (isset($_POST['unidad_limite'])) {
        echo '<input type="hidden" name="unidad_limite" value="' . intval($_POST['unidad_limite']) . '">';
        echo '<p><strong>Unidad evaluada:</strong> Hasta la unidad ' . intval($_POST['unidad_limite']) . '</p>';
        echo '<button type="submit" name="modificar_unidad">Modificar unidad evaluada</button><br><br>';
    }

    echo '<button type="submit" name="cargar_materia">Cargar</button>';
    echo '</form>';

    if (isset($_POST['guardar_calificaciones'])) {
        $id_materia = intval($_POST['id_materia']);
        $unidad_limite = intval($_POST['unidad_limite']);
        $calificaciones = $_POST['calificacion'];
        $faltas = $_POST['faltas'];

        foreach ($calificaciones as $id_alumno => $por_unidad) {
            foreach ($por_unidad as $unidad => $cal) {
                if ($unidad > $unidad_limite) continue; // No procesar unidades mayores

                $falta = $faltas[$id_alumno][$unidad];

                $id_asigma = $wpdb->get_var($wpdb->prepare(
                    "SELECT id_asigma FROM asignacionmateria 
                     WHERE id_alumno = %d AND id_materia = %d",
                     $id_alumno, $id_materia
                ));

                if ($id_asigma) {
                    $existe = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM UnidadMateria WHERE id_asigma = %d AND unidad = %d",
                        $id_asigma, $unidad
                    ));

                    if ($existe) {
                        $wpdb->update('UnidadMateria',
                            [ 'calificacion' => $cal, 'faltas' => $falta ],
                            [ 'id_asigma' => $id_asigma, 'unidad' => $unidad ]
                        );
                    } else {
                        $wpdb->insert('UnidadMateria', [
                            'id_asigma' => $id_asigma,
                            'unidad' => $unidad,
                            'calificacion' => $cal,
                            'faltas' => $falta
                        ]);
                    }
                }
            }
        }

        echo '<p>✅ Calificaciones y faltas guardadas correctamente.</p>';
    }

    if (isset($_POST['cargar_materia']) || isset($_POST['guardar_calificaciones'])) {
        $id_materia = intval($_POST['id_materia']);
        $unidad_limite = intval($_POST['unidad_limite'] ?? 6);

        $alumnos = $wpdb->get_results($wpdb->prepare(
            "SELECT a.id_alumno, a.nombre, a.apellido_pat, a.apellido_mat
             FROM alumno a
             JOIN asignacionmateria am ON a.id_alumno = am.id_alumno
             WHERE a.id_grupo = %d AND am.id_materia = %d",
            $grupo_id, $id_materia
        ));

        echo '<form method="post">';
        echo '<input type="hidden" name="id_materia" value="' . esc_attr($id_materia) . '">';
        echo '<input type="hidden" name="unidad_limite" value="' . esc_attr($unidad_limite) . '">';
        echo '<table border="1" style="width:100%; text-align:center;">';
        echo '<tr><th>Alumno</th>';
        for ($u = 1; $u <= $unidad_limite; $u++) {
            echo '<th>U' . $u . ' Cal</th><th>U' . $u . ' Faltas</th>';
        }
        echo '</tr>';

        foreach ($alumnos as $a) {
            $nombre = esc_html("$a->nombre $a->apellido_pat $a->apellido_mat");
            echo '<tr><td>' . $nombre . '</td>';
            for ($u = 1; $u <= $unidad_limite; $u++) {
                $cal = $wpdb->get_var($wpdb->prepare(
                    "SELECT calificacion FROM UnidadMateria
                     WHERE id_asigma = (SELECT id_asigma FROM asignacionmateria WHERE id_alumno = %d AND id_materia = %d)
                       AND unidad = %d",
                    $a->id_alumno, $id_materia, $u
                ));
                $faltas = $wpdb->get_var($wpdb->prepare(
                    "SELECT faltas FROM UnidadMateria
                     WHERE id_asigma = (SELECT id_asigma FROM asignacionmateria WHERE id_alumno = %d AND id_materia = %d)
                       AND unidad = %d",
                    $a->id_alumno, $id_materia, $u
                ));
                echo '<td><input type="text" class="input-cal" inputmode="decimal" pattern="[0-9]*" name="calificacion[' . $a->id_alumno . '][' . $u . ']" value="' . esc_attr($cal) . '" maxlength="3"></td>';
                echo '<td><input type="text" class="input-faltas" inputmode="numeric" pattern="[0-9]*" name="faltas[' . $a->id_alumno . '][' . $u . ']" value="' . esc_attr($faltas) . '" maxlength="3"></td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        echo '<button type="submit" name="guardar_calificaciones">Guardar</button>';
        echo '</form>';
        ?>
        <script>
        document.querySelectorAll("input[type=text]").forEach((input, i, all) => {
            input.addEventListener("keydown", e => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const next = all[i + 1];
                    if (next) next.focus();
                }
            });
            input.addEventListener("input", e => {
                const val = parseFloat(e.target.value);
                if (e.target.classList.contains("input-cal")) {
                    e.target.style.backgroundColor = (val > 100 || val < 0 || isNaN(val)) ? "#fbb" : "white";
                } else if (e.target.classList.contains("input-faltas")) {
                    e.target.style.backgroundColor = (val < 0 || isNaN(val)) ? "#fbb" : "white";
                }
            });
        });

        document.querySelector("form").addEventListener("submit", function(e) {
            let error = false;
            document.querySelectorAll("input[type=text]").forEach(input => {
                const val = input.value.trim();
                if (val !== "" && isNaN(val)) {
                    error = true;
                    input.style.backgroundColor = "#fbb";
                }
            });
            if (error) {
                alert("⚠️ Solo se permiten números en calificaciones y faltas.");
                e.preventDefault();
            }
        });
        </script>
        <?php
    }

    return ob_get_clean();
}
