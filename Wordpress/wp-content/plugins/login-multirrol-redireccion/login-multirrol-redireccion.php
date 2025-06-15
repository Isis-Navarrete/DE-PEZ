<?php
/**
 * Plugin Name: Login Multirrol con Redirección Final
 * Description: Login real que guarda múltiples roles y redirige según prioridad: jefe de carrera, tutor, docente.
 * Version:     2.4
 * Author:      Juan Taniz
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! session_id() ) session_start();

add_shortcode( 'login_real', 'login_multirrol_render' );

function login_multirrol_render() {
    global $wpdb;

    $redireccionJS = '';

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correo'], $_POST['contrasena']) ) {
        $correo   = strtolower( trim( $_POST['correo'] ) );
        $password = trim( $_POST['contrasena'] );

        // 1) Obtener usuario
        $usuario = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM usuario WHERE LOWER(correo) = %s AND contrasena = %s",
            $correo, $password
        ) );

        if ( $usuario ) {
            // 2) Obtener TODOS los roles asignados a este usuario
            $roles = $wpdb->get_col( $wpdb->prepare(
                "SELECT id_rol FROM usuariorol WHERE id_usuario = %d",
                $usuario->id_usuario
            ) );

            // 3) Guardar en sesión
            $_SESSION['usuario_id']     = $usuario->id_usuario;
            $_SESSION['usuario_nombre'] = $usuario->nombre;
            $_SESSION['usuario_roles']  = $roles;

            // 4) Redirecciones con prioridad
            if ( in_array( 3, $roles ) ) {
                // Jefe de carrera
                $redireccionJS = "window.location.href='https://nonstop-taniz.mnz.dom.my.id/?page_id=60';";
            }
            elseif ( in_array( 2, $roles ) ) {
                // Tutor → primero al menú docente + params
                $redireccionJS = "window.location.href='https://nonstop-taniz.mnz.dom.my.id/"
                               . "?page_id=32&user={$usuario->id_usuario}&role=2';";
            }
            elseif ( in_array( 1, $roles ) ) {
                // Docente
                $redireccionJS = "window.location.href='https://nonstop-taniz.mnz.dom.my.id/?page_id=32';";
            }
            else {
                echo "<div style='color:red;text-align:center;'>⚠️ Este rol aún no tiene acceso definido.</div>";
            }
        }
        else {
            echo "<div style='color:red;text-align:center;'>❌ Usuario o contraseña incorrectos.</div>";
        }
    }

    // 5) Formulario de login
    ob_start(); ?>
    <form method="POST" style="max-width:400px;margin:auto;padding:20px;border:1px solid #ccc;background:#f9f9f9;">
        <h3 style="text-align:center;margin-bottom:15px;">Iniciar Sesión DEPEZ</h3>
        <label>Correo:</label>
        <input type="email" name="correo" required style="width:100%;padding:8px;margin-bottom:10px;">
        <label>Contraseña:</label>
        <input type="password" name="contrasena" required style="width:100%;padding:8px;margin-bottom:15px;">
        <button type="submit" style="width:100%;padding:10px;">Entrar</button>
    </form>
    <?php
    // 6) Inyectar la redirección en JavaScript
    if ( $redireccionJS ) {
        echo "<script>{$redireccionJS}</script>";
    }

    return ob_get_clean();
}
/* ==========================================================
   SHORTCODE: BOTÓN DE CERRAR SESIÓN [cerrar_sesion_usuario]
========================================================== */

add_shortcode('cerrar_sesion_usuario', 'mostrar_boton_cerrar_sesion');

function mostrar_boton_cerrar_sesion() {
    if (!isset($_SESSION['usuario_id'])) return '';

    $nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

    ob_start(); ?>
    <div style="position:fixed;top:15px;right:15px;z-index:9999;background:#fff;padding:8px 12px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.2);font-family:sans-serif;">
        👤 <?= esc_html($nombre_usuario) ?>
        <form method="post" style="display:inline;">
            <button name="cerrar_sesion" style="background:#d33;color:white;border:none;padding:5px 10px;border-radius:5px;margin-left:10px;cursor:pointer;">
                Cerrar sesión
            </button>
        </form>
    </div>
    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_sesion'])) {
        $_SESSION = array();
        session_destroy();

        // Evitar volver con botón atrás
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        echo "<script>
            window.location.href='https://nonstop-taniz.mnz.dom.my.id/?page_id=289';
            if (window.history && history.pushState) {
                history.pushState(null, null, location.href);
                window.onpopstate = function () {
                    history.go(1);
                };
            }
        </script>";
        exit;
    }

    return ob_get_clean();
}
