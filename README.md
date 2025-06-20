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
- Plugin personalizado: registrar-docente(Shortcode:[formulario_registro_docente])
- Plugin personalizado: modificar-docente(Shortcode:[formulario_editar_docente])
- Plugin personalizado: eliminar-docente(Shortcode:[formulario_eliminar_docente])

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

 ⚙️ Instalación Local
Requisitos Previos
Antes de iniciar con el procedimiento, se debe verificar que el equipo cuente con las siguientes aplicaciones instaladas:
- XAMPP    
- Git  
- Visual Studio Code  
- Navegador web  

> **Advertencia**:  
> Al momento de activar Apache o MySQL podrían surgir problemas. Se recomienda configurar correctamente los puertos para asegurar el buen funcionamiento del sistema.  
> Como alternativa, se puede ejecutar la página en un equipo donde no se haya instalado XAMPP previamente.

 Paso 1. Clonar el Repositorio
1. Abrir Visual Studio Code.  
2. Abrir la terminal desde el menú superior.  
3. Pegar el enlace del repositorio: https://github.com/Isis-Navarrete/DE-PEZ.git
4. Dirigirse al explorador de archivos e ingresar a la carpeta con el siguiente comando: cd DE-PEZ
5. Acceder a la carpeta DE-PEZ\wordpress\ y copiar la carpeta completa.

 Verificar que estén presentes los siguientes archivos:
- wp-config.php  
- index.php
- Carpeta wp-content (con plugins y temas)  
- Archivo .htaccess  

 Paso 2. Importar la Base de Datos
1. Abrir phpMyAdmin desde: http://localhost/phpmyadmin
2. Crear una base de datos con el nombre: nonstop_taniz_db
3. Hacer clic en la opción Importar y seleccionar el archivo: DE-PEZ/nonstop_taniz_db.sql
4. Esperar a que se importen todas las tablas sin errores.

 Paso 3. Configurar wp-config.php
1. Abrir el archivo en la siguiente ruta: C:\xampp\htdocs\DE-PEZ\Wordpress\wp-config.php
2. Verificar que contenga la siguiente configuración (editar en caso necesario): php
- define('DB_NAME', 'nonstop_taniz_db');
- define('DB_USER', 'root');
- define('DB_PASSWORD', '');
- define('DB_HOST', 'localhost');

Usuario root y contraseña vacía son los valores por defecto en XAMPP.  
En el código de wp-config.php vienen más indicaciones.

 Paso 4. Iniciar Apache y MySQL
1. Abrir el panel de control de XAMPP.  
2. Iniciar Apache y MySQL.  
3. Asegurarse de que no haya errores.
>  Si Apache falla, verificar que los puertos 80 o 443 no estén en uso.

Paso 5. Acceder a la Página
1. Abrir un navegador web e ingresar al siguiente enlace: http://localhost/DE-PEZ/Wordpress/
2. Desde allí se puede iniciar sesión con los usuarios ya registrados en la base de datos.

 Usuarios de prueba para iniciar sesión:
Docente/Tutor  
- Correo: laura.ramirez@itspc.edu.mx
- Contraseña: pass654

Jefe de carrera  
- Correo: PabloUli@gmail.com  
- Contraseña: 120211234

 Usuario para acceder a WordPress (Rol administrador):
- Usuario: juan.salazar.22isc@tecsanpedro.edu.mx  
- Contraseña: 8)tBa4mgjsEyXDqvb3

 Usuario para acceder a PHPMyAdmin:
- Usuario: nonstop-taniz  
- Contraseña: 4qE_I-aD67q61D(Nun

Contacto
Para acceder al dominio donde está alojada la página o el administrador de archivos en Webmin, contactar a:

Juan Taniz Salazar Franco  
- Correo: juan.salazar.22isc@tecsanpedro.edu.mx  
- Teléfono: 8721214433 (solo WhatsApp)


> Proyecto académico desarrollado para la Unidad 4: Documentación Técnica
> Docente: Ruth Aivi Chávez Rodríguez | Ingeniería en Sistemas Computacionales | Junio 2025
