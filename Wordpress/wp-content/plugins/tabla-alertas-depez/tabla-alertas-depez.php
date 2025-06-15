<?php
/**
 * Plugin Name: Tabla de Alertas DEPEZ (v2.5 con diagnóstico)
 * Description: Muestra calificaciones/faltas por unidad y permite alertas. Incluye depuración visual por alumno.
 * Version: 2.5
 * Author: Juan Taniz
 */

if (!defined('ABSPATH')) exit;
if (!session_id()) session_start();

add_shortcode('tabla_alertas_depez', 'mostrar_tabla_alertas_depez');

function mostrar_tabla_alertas_depez() {
    global $wpdb;

    if (!isset($_SESSION['usuario_id'], $_GET['materia'], $_GET['grupo'], $_GET['semestre'])) {
        return "<div style='color:red;'>🚫 Debes iniciar sesión para ver esta información.</div>";
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $roles = $_SESSION['usuario_roles'] ?? [];
    $materia_id = intval($_GET['materia']);
    $grupo_nombre = sanitize_text_field($_GET['grupo']);
    $semestre = sanitize_text_field($_GET['semestre']);
    $modo = $_GET['modo'] ?? 'calificaciones';

    if (!in_array(2, $roles) && !in_array(3, $roles)) {
        return "<div style='color:red;'>⛔ Solo tutores o jefes de carrera pueden acceder a esta página.</div>";
    }

    $grupo_id = $wpdb->get_var($wpdb->prepare("SELECT id_grupo FROM grupo WHERE nombre = %s", $grupo_nombre));
    if (!$grupo_id) return "<div style='color:orange;'>⚠️ El grupo especificado no existe.</div>";

    $alumnos = $wpdb->get_results($wpdb->prepare(
        "SELECT a.id_alumno, a.nombre, a.apellido_pat, a.apellido_mat, am.id_asigma, am.id_estado
         FROM alumno a
         JOIN asignacionmateria am ON a.id_alumno = am.id_alumno
         WHERE a.id_grupo = %d AND am.id_materia = %d", $grupo_id, $materia_id
    ));

    if (!$alumnos) return "<div style='color:orange;'>⚠️ No hay registros para este grupo, materia y semestre.</div>";

    $materia = $wpdb->get_var($wpdb->prepare("SELECT nombre FROM materia WHERE id_materia = %d", $materia_id));
    $nombre_usuario = $wpdb->get_var($wpdb->prepare("SELECT CONCAT(nombre, ' ', apellido_pat) FROM usuario WHERE id_usuario = %d", $usuario_id));

    ob_start(); ?>
    <div style="margin-bottom:20px;">
        <strong>Materia:</strong> <?= esc_html($materia) ?> |
        <strong>Grupo:</strong> <?= esc_html($grupo_nombre) ?> |
        <strong>Usuario:</strong> <?= esc_html($nombre_usuario) ?>
    </div>

    <form method="GET" style="margin-bottom:10px;">
        <?php foreach ($_GET as $k => $v) {
            if ($k !== 'modo') echo "<input type='hidden' name='".esc_attr($k)."' value='".esc_attr($v)."'>";
        } ?>
        <button type="submit" name="modo" value="<?= $modo === 'faltas' ? 'calificaciones' : 'faltas' ?>" style="padding:8px 16px;">
            Ver <?= $modo === 'faltas' ? 'Calificaciones' : 'Faltas' ?>
        </button>
    </form>

            
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#ddd;">
                <th>Alumno</th>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <th><?= $modo === 'faltas' ? "Faltas U$i" : "Calif. U$i" ?></th>
                <?php endfor; ?>
                <th>Estado</th>
                <th>Alerta</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($alumnos as $a):
            $nombre = "{$a->nombre} {$a->apellido_pat} {$a->apellido_mat}";
            $estado = $wpdb->get_var($wpdb->prepare("SELECT estado FROM estadoalumno WHERE id_estado = %d", $a->id_estado));

            $datos_unidad = array_fill(1, 6, ['calificacion' => '-', 'faltas' => '-']);
            $unidades = $wpdb->get_results(
                $wpdb->prepare("SELECT unidad, calificacion, faltas 
                                FROM UnidadMateria 
                                WHERE id_asigma = %d 
                                ORDER BY id_unidad DESC", $a->id_asigma)
            );
// probablemente necesite descomentar este fragmento
            // 🔎 DEPURACIÓN: Mostrar lo que trae UnidadMateria
           /* echo "<div style='background:#eef;padding:10px;margin-bottom:5px;'>
                <strong>Alumno:</strong> $nombre | <strong>ID Asignación:</strong> {$a->id_asigma}<br>";
            if ($unidades) {
                foreach ($unidades as $u) {
                    echo "Unidad: {$u->unidad} | Calificación: {$u->calificacion} | Faltas: {$u->faltas}<br>";
                }
            } else {
                echo "❌ No hay registros en UnidadMateria para este alumno.<br>";
            }
            echo "</div>";*/

            foreach ($unidades as $u) {
                $u_num = intval($u->unidad);
                if (!is_numeric($datos_unidad[$u_num]['calificacion']) || $datos_unidad[$u_num]['calificacion'] === '-') {
                    $datos_unidad[$u_num] = [
                        'calificacion' => floatval($u->calificacion),
                        'faltas' => intval($u->faltas)
                    ];
                }
            }

            $reprobado = false;
            foreach ($datos_unidad as $u) {
                if (is_numeric($u['calificacion']) && $u['calificacion'] < 70) {
                    $reprobado = true;
                    break;
                }
            }
            ?>
            <tr>
                <td><?= esc_html($nombre) ?></td>
                <?php for ($i = 1; $i <= 6; $i++):
                    $valor = ($modo === 'faltas') ? $datos_unidad[$i]['faltas'] : $datos_unidad[$i]['calificacion'];
                    $is_reprobado = is_numeric($valor) && $modo === 'calificaciones' && $valor < 70; ?>
                    <td style="text-align:center; <?= $is_reprobado ? 'background:#fbb;' : '' ?>">
                        <?= esc_html($valor) ?>
                    </td>
                <?php endfor; ?>
                <td><?= $reprobado ? 'Segunda oportunidad' : 'Regular' ?></td>
                <td>
                    <?php if ($reprobado): ?>
                        <form method="POST" class="form-alerta">
                            <input type="hidden" name="id_alumno" value="<?= $a->id_alumno ?>">
                            <input type="hidden" name="id_materia" value="<?= $materia_id ?>">
                            <button type="submit" name="enviar_alerta" class="boton-alerta" style="padding:4px 10px;background:red;color:white;border:none;border-radius:5px;">
                                Enviar alerta
                            </button>
                        </form>
                    <?php else: ?> - <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        
    </table>
        
        <div style="margin-top: 20px;">
    <a href="javascript:history.back()" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #e60000;
        color: white;
        font-family: Arial, sans-serif;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
    ">
        Regresar
    </a>
</div>
        
    <script>
    document.querySelectorAll('.form-alerta').forEach(form => {
        form.addEventListener('submit', function (e) {
            const btn = form.querySelector('.boton-alerta');
            btn.disabled = true;
            btn.innerText = 'Enviada';
            btn.style.background = '#aaa';
        });
    });
    </script>
    <?php
    //Comentado por si las dudas 12/06/25
    //if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_alerta'])) {
      //  echo "<div style='color:green;margin-top:15px;'>📧 Alerta enviada (simulada) al alumno ID: {$_POST['id_alumno']} por la materia ID: {$_POST['id_materia']}.</div>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_alerta'])) {
    $id_alumno = intval($_POST['id_alumno']);
    $id_materia = intval($_POST['id_materia']);

    // Obtener correo y nombre del alumno
    $alumno = $wpdb->get_row($wpdb->prepare("SELECT nombre, apellido_pat, correo FROM alumno WHERE id_alumno = %d", $id_alumno));
    $materia = $wpdb->get_var($wpdb->prepare("SELECT nombre FROM materia WHERE id_materia = %d", $id_materia));

    if ($alumno && $alumno->correo) {
        $nombre_alumno = "{$alumno->nombre} {$alumno->apellido_pat}";
        $asunto = "🔔 Alerta académica en la materia $materia";
        $mensaje = "Estimado(a) $nombre_alumno:\n\nSe ha detectado que tu desempeño en la materia \"$materia\" requiere atención, ya que tienes al menos una calificación menor a 70.\n\nPor favor, acércate a tu docente o tutor para recibir orientación.\n\nAtentamente,\nSistema DEPEZ";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        if (wp_mail($alumno->correo, $asunto, $mensaje, $headers)) {
            echo "<div style='color:green;margin-top:15px;'>📧 Alerta enviada exitosamente a <strong>{$alumno->correo}</strong>.</div>";
        } else {
            echo "<div style='color:red;margin-top:15px;'>❌ Error al enviar el correo a {$alumno->correo}.</div>";
        }
    } else {
        echo "<div style='color:red;margin-top:15px;'>❌ No se pudo encontrar el correo del alumno con ID $id_alumno.</div>";
    }
}

    return ob_get_clean();
}
