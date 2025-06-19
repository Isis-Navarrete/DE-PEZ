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

⚙️Instalación local
Instalación local
-Antes de iniciar con el procedimiento, se debe verificar que el equipo cuente con las siguientes aplicaciones instaladas:
•	XAMPP 
•	Apache
•	MySQL
•	Git

Advertencia: Al momento de activar Apache o MySQL podrían surgir problemas; por lo tanto, se recomienda configurar correctamente los puertos para asegurar el buen funcionamiento del sistema. Como alternativa, se puede ejecutar la página en un equipo donde no se haya instalado XAMPP previamente.
•	Visual Studio Code
•	Navegador web

-Paso 1. Clonar el repositorio
Se debe clonar el repositorio en el equipo:
•	Abrir el programa Visual Studio Code
•	Abrir la terminal desde el menú superior
•	Pegar el enlace del repositorio: https://github.com/Isis-Navarrete/DE-PEZ.git
•	Una vez hecho esto, dirigirse al explorador de archivos e ingresar a la carpeta con el siguiente comando: cd DE-PEZ
•	Luego, desde el explorador de archivos, acceder a la carpeta DE-PEZ\wordpress\ y copiar la carpeta completa
-Se debe verificar que estén presentes los siguientes archivos:
•	wp-config.php
•	index.php
•	Carpeta wp-content con plugins y temas
•	Archivo .htaccess

-Paso 2. Importar la base de datos
Se debe abrir phpMyAdmin desde http://localhost/phpmyadmin
Crear una base de datos con el nombre: nonstop_taniz_db
Hacer clic en la opción "Importar" y seleccionar el archivo: DE-PEZ/nonstop_taniz_db.sql
Esperar a que se importen todas las tablas sin errores.

-Paso 3. Configurar wp-config.php
Una vez importada la base de datos, se debe abrir el archivo ubicado en C:\xampp\htdocs\DE-PEZ\Wordpress\wp-config.php
Verificar que contenga la siguiente configuración (editar en caso necesario):
define('DB_NAME', 'nonstop_taniz_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
(Usuario root y contraseña vacía son los valores por defecto en XAMPP)
(En el código del wp-config vienen mas indicaciones)

-Paso 4. Iniciar Apache y MySQL
Se debe abrir el panel de control de XAMPP e iniciar Apache y MySQL. Asegurarse de que no haya errores. Si Apache falla, verificar que los puertos 80 o 443 no estén en uso por otros servicios.

-Paso 5. Acceder a la página
Abrir un navegador web e ingresar al siguiente enlace:
http://localhost/DE-PEZ/Wordpress/
Desde allí se puede iniciar sesión con los usuarios ya registrados en la base de datos.
Usuarios de prueba para iniciar sesión:
•	Docente/Tutor
-Correo: laura.ramirez@itspc.edu.mx
Contraseña: pass654
•	Jefe de carrera
Correo: PabloUli@gmail.com
Contraseña: 120211234

Usuario para acceder a WordPress (Rol administrador):
•	Usuario: juan.salazar.22isc@tecsanpedro.edu.mx
•	Contraseña: 8)tBa4mgjsEyXDqvb3

Usuario para acceder a PHPMyAdmin:
•	Usuario: nonstop-taniz
•	Contraseña: 4qE_I-aD67q61D(Nun

NOTA: Para acceder al dominio donde esta alojado la pagina o el administrador de archivos en webmin, contactar a Juan Taniz Salazar Franco
Medios de contacto:
•	Correo : juan.salazar.22isc@tecsanpedro.edu.mx
•	Teléfono : 8721214433 (solo WhatsApp)



> Proyecto académico desarrollado para la Unidad 4: Documentación Técnica
> Docente: Ruth Aivi Chávez Rodríguez | Ingeniería en Sistemas Computacionales | Junio 2025
