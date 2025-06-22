<?php
/**
 * Plugin Name: Tabla de Alertas DEPEZ (SMTP2GO JS v4.1)
 * Description: Muestra calificaciones por unidad y permite enviar alertas por correo vía SMTP2GO desde el navegador (JS).
 * Version: 4.1
 * Author: Juan Taniz
 */

// Seguridad: Salir si se accede directamente
if (!defined('ABSPATH')) exit;

// Iniciar sesión si no está activa
if (!session_id()) session_start();

// Registrar shortcode
add_shortcode('tabla_alertas_depez', 'mostrar_tabla_alertas_depez');

function mostrar_tabla_alertas_depez() {
    global $wpdb;

    // Verificar autenticación y parámetros requeridos
    if (!isset($_SESSION['usuario_id'], $_GET['materia'], $_GET['grupo'], $_GET['semestre'])) {
        return "<div style='color:red;'>🚫 Debes iniciar sesión para ver esta información.</div>";
    }

    // Obtener y sanitizar datos del usuario
    $usuario_id = intval($_SESSION['usuario_id']);
    $roles = $_SESSION['usuario_roles'] ?? [];
    $materia_id = intval($_GET['materia']);
    $grupo_nombre = sanitize_text_field($_GET['grupo']);
    $semestre = sanitize_text_field($_GET['semestre']);
    $modo = $_GET['modo'] ?? 'calificaciones';

    // Verificar permisos (solo tutores o jefes de carrera)
    if (!in_array(2, $roles) && !in_array(3, $roles)) {
        return "<div style='color:red;'>⛔ Solo tutores o jefes de carrera pueden acceder a esta página.</div>";
    }

    // Obtener ID del grupo
    $grupo_id = $wpdb->get_var($wpdb->prepare("SELECT id_grupo FROM grupo WHERE nombre = %s", $grupo_nombre));
    if (!$grupo_id) return "<div style='color:orange;'>⚠️ El grupo especificado no existe.</div>";

    // Obtener alumnos de la materia
    $alumnos = $wpdb->get_results($wpdb->prepare(
        "SELECT a.id_alumno, a.nombre, a.apellido_pat, a.apellido_mat, a.correo, am.id_asigma, am.id_estado 
         FROM alumno a 
         JOIN asignacionmateria am ON a.id_alumno = am.id_alumno 
         WHERE a.id_grupo = %d AND am.id_materia = %d", 
        $grupo_id, $materia_id
    ));

    if (!$alumnos) return "<div style='color:orange;'>⚠️ No hay alumnos en esta materia.</div>";

    // Obtener información adicional
    $materia = $wpdb->get_var($wpdb->prepare("SELECT nombre FROM materia WHERE id_materia = %d", $materia_id));
    $nombre_usuario = $wpdb->get_var($wpdb->prepare(
        "SELECT CONCAT(nombre, ' ', apellido_pat) FROM usuario WHERE id_usuario = %d", 
        $usuario_id
    ));

    ob_start(); // Iniciar buffer de salida
    ?>
    <!-- Encabezado con información básica -->
    <div style="margin-bottom:20px;">
        <strong>Materia:</strong> <?= esc_html($materia) ?> |
        <strong>Grupo:</strong> <?= esc_html($grupo_nombre) ?> |
        <strong>Usuario:</strong> <?= esc_html($nombre_usuario) ?>
    </div>

    <!-- Selector de modo (calificaciones/faltas) -->
    <form method="GET" style="margin-bottom:10px;">
        <?php foreach ($_GET as $k => $v): if ($k !== 'modo'): ?>
            <input type="hidden" name="<?= esc_attr($k) ?>" value="<?= esc_attr($v) ?>">
        <?php endif; endforeach; ?>
        <button type="submit" name="modo" value="<?= $modo === 'faltas' ? 'calificaciones' : 'faltas' ?>" style="padding:8px 16px;">
            Ver <?= $modo === 'faltas' ? 'Calificaciones' : 'Faltas' ?>
        </button>
    </form>

    <!-- Tabla principal -->
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
        <?php
        $alertas_js = []; // Almacenar datos para JS

        foreach ($alumnos as $alumno):
            $nombre = "$alumno->nombre $alumno->apellido_pat $alumno->apellido_mat";
            $estado = $wpdb->get_var($wpdb->prepare(
                "SELECT estado FROM estadoalumno WHERE id_estado = %d", 
                $alumno->id_estado
            ));

            // Obtener datos por unidad
            $datos_unidad = array_fill(1, 6, ['calificacion' => '-', 'faltas' => '-']);
            $unidades = $wpdb->get_results($wpdb->prepare(
                "SELECT unidad, calificacion, faltas FROM UnidadMateria WHERE id_asigma = %d ORDER BY unidad", 
                $alumno->id_asigma
            ));

            // Generar mensaje para alumnos reprobados
            $reprobado = false;
            $mensaje = "Estimado(a) $nombre,\n\nSe detectaron calificaciones reprobadas en la materia \"$materia\".\n\n";
            
            foreach ($unidades as $u) {
                $u_num = intval($u->unidad);
                $datos_unidad[$u_num] = [
                    'calificacion' => floatval($u->calificacion),
                    'faltas' => intval($u->faltas)
                ];
                
                if ($u->calificacion < 70) {
                    $reprobado = true;
                    $mensaje .= "Unidad {$u->unidad}: Calificación: {$u->calificacion}, Faltas: {$u->faltas}\n";
                }
            }
            
            $mensaje .= "\nPor favor, acércate a tu docente o tutor para orientación.\n\nSistema DEPEZ";
            ?>
            <tr>
                <td><?= esc_html($nombre) ?></td>
                <?php for ($i = 1; $i <= 6; $i++):
                    $valor = ($modo === 'faltas') ? $datos_unidad[$i]['faltas'] : $datos_unidad[$i]['calificacion'];
                    $is_reprobado = is_numeric($valor) && $modo === 'calificaciones' && $valor < 70;
                    ?>
                    <td style="text-align:center; <?= $is_reprobado ? 'background:#fbb;' : '' ?>">
                        <?= esc_html($valor) ?>
                    </td>
                <?php endfor; ?>
                <td><?= $reprobado ? 'Segunda oportunidad' : esc_html($estado) ?></td>
                <td>
                    <?php if ($reprobado):
                        $alertas_js[] = [
                            'correo' => $alumno->correo,
                            'nombre' => $nombre,
                            'mensaje' => $mensaje
                        ];
                        ?>
                        <button class="boton-alerta" data-correo="<?= esc_attr($alumno->correo) ?>">
                            Enviar alerta
                        </button>
                    <?php else: ?> - <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Botón de regreso -->
    <div style="margin-top: 20px;">
        <a href="javascript:history.back()" style="padding: 10px 20px; background-color: #e60000; color: white; font-family: Arial, sans-serif; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Regresar
        </a>
    </div>

    <!-- Script para manejar el envío de alertas -->
    <script>
    const alertas = <?= json_encode($alertas_js) ?>;
    const apiKey = "api-F4AC1FECBAE546969814973A180A05E4"; // ⚠ Sustituye por una clave segura en producción

    document.querySelectorAll('.boton-alerta').forEach(btn => {
        btn.addEventListener('click', async function () {
            const correo = this.dataset.correo;
            const alerta = alertas.find(a => a.correo === correo);
            if (!alerta) return;

            this.disabled = true;
            this.innerText = 'Enviando...';

            const payload = {
                api_key: apiKey,
                to: [correo],
                sender: "juan.salazar.22isc@tecsanpedro.edu.mx",
                sender_name: "Sistema DEPEZ",
                subject: "Alerta académica",
                text_body: alerta.mensaje
            };

            try {
                const res = await fetch("https://api.smtp2go.com/v3/email/send", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data && data.data && data.data.succeeded === 1) {
                    this.innerText = 'Alerta enviada';
                    this.style.background = '#aaa';
                } else {
                    console.error("Error SMTP2GO:", data);
                    this.innerText = 'Error';
                    this.style.background = 'orange';
                    alert("❌ Error al enviar correo:\n" + JSON.stringify(data, null, 2));
                }
            } catch (err) {
                console.error(err);
                this.innerText = 'Error';
                this.style.background = 'orange';
                alert("❌ Error de conexión al servidor SMTP2GO");
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
?>
