Plataforma de Seguimiento Escolar - DE-PEZ

Este proyecto fue desarrollado por estudiantes del Instituto Tecnológico Superior de las Colonias como parte del curso de Ingeniería de Software. Se trata de una plataforma web que permite a docentes, tutores y jefes de carrera administrar calificaciones, materias y el desempeño académico de los estudiantes.

🎯 Objetivo
Brindar a los docentes una herramienta intuitiva para agregar, modificar o eliminar información académica, además de monitorear alertas de desempeño estudiantil, todo desde un panel personalizado según su rol.

🧱 Tecnologías utilizadas
- WordPress (como CMS)
- Plugins: Elementor, Forminator, Hello Plus, Duplicator,WP Mail SMTP
- HTML, CSS, JavaScript, PHP
- Base de datos MySQL (phpMyAdmin)
- Servidor: DomCloud
- Pliguin personalizado: Alerta academica (Shortcode: [menu_tutor_grupo])
- Pluguin personalizado: login-multirol-redaccion (shortcode: [ login_real])
- Pluguin personalizado: menu-tutor (Shortcode:[menu_tutor_grupo])
- Pluguin personalizado: tabla-alertas(Shortcode:[tabla_alertas_depez
- pluguin personalizado: agregar-alumno (Sortcode:[formulario_registro_alumno])
- Pluguin personalizado: modificar-alumno(Shortcode:[modificar_alumno_from])
- Plugin personalizado: eliminar-alumno(Shortcode:[formulario_eliminar_alumno])

🔐 Roles del sistema
- *Jefe de carrera*: Control total, vista global de calificaciones y profesores.
- *Docente*: Solo visualiza y modifica calificaciones de su grupo.
- *Tutor*: Puede ver y enviar alertas académicas a sus tutorados.

📁 Funcionalidades clave
- Inicio de sesión por rol.
- Formularios personalizados para CRUD de alumnos y profesores.
- Visualización de materias y grupos asignados.
- Tabla dinámica para alertas académicas.
- Validación de datos y seguridad mediante PHP.

✅ Estado del desarrollo
La mayoría de las funciones se encuentran implementadas y probadas. Algunos módulos como el formulario de eliminación aún presentan errores menores que están siendo solucionados.

👨‍💻 Autores
- Isis Regina Díaz Navarrete
- Juan Taniz Salazar Franco
- Brian Guadalupe Fernández
- Brandon Gael Medina Martínez
- José Ignacio Ramirez Hernández

⚙️ Instalación local
1.-Clona este repositorio en tu equipo:

git clone https://github.com/Isis-Navarrete/DE-PEZ.git

2.-Abre el proyecto en Visual Studio Code o tu editor favorito.

3.-Instala un servidor local (por ejemplo, XAMPP o Laragon) con PHP y MySQL.

4.-Copia la carpeta wordpress/ dentro de la carpeta pública de tu servidor:

   -En XAMPP: C:\xampp\htdocs\depez

   -En Laragon: C:\laragon\www\depez

5.-Abre phpMyAdmin y crea una nueva base de datos:


CREATE DATABASE depez_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

6.-Importa el archivo SQL ubicado en:

/databases/nonstop_taniz_db.sql

Puedes hacerlo desde phpMyAdmin o con este comando (si usas terminal):

mysql -u root -p depez_db < databases/nonstop_taniz_db.sql

7.-Configura el archivo wp-config.php:

Ve a:

/wordpress/wp-config.php

Y asegúrate de que tenga estos valores (editando los reales si hace falta):


Editar
define( 'DB_NAME', 'depez_db' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' ); // Si usas XAMPP, déjalo vacío
define( 'DB_HOST', 'localhost' );

8.-Accede desde tu navegador a:

http://localhost/depez

¡Listo! Ya puedes iniciar sesión con los roles existentes.

> Proyecto académico desarrollado para la Unidad 4: Documentación Técnica
> Docente: Ruth Aivi Chávez Rodríguez | Ingeniería en Sistemas Computacionales | Junio 2025
