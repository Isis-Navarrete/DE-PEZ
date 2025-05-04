--Crear la base de datos
CREATE DATABASE DE_PEZ
GO
USE DE_PEZ
GO

-- Creación del catálogo de roles
CREATE TABLE Rol (
    id_rol INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(30) NOT NULL
)

-- Insertar roles predefinidos
INSERT INTO Rol (nombre) VALUES 
('Profesor'),
('Tutor'),
('Jefe de Carrera')

-- Creación del catálogo de estados de alumnos
CREATE TABLE EstadoAlumno (
    id_estado INT PRIMARY KEY IDENTITY(1,1),
    estado VARCHAR(30) NOT NULL
)


-- Insertar estados predefinidos de alumnos
INSERT INTO EstadoAlumno (estado) VALUES 
('Regular'),
('Segunda Oportunidad'),
('Tercera Oportunidad')


-- Tabla de Usuarios
CREATE TABLE Usuario (
    id_usuario INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(50) NOT NULL,
    apellido_mat VARCHAR(50) NOT NULL,
	apellido_pat VARCHAR(50) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    contrasena VARCHAR(10) NOT NULL,
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol)
)


-- Tabla de Grupo
CREATE TABLE Grupo (
    id_grupo INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(20) NOT NULL
)


-- Tabla de Materia
CREATE TABLE Materia (
    id_materia INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(50) NOT NULL,
    creditos INT NOT NULL,
    semestre VARCHAR(20) NOT NULL,
)
Select * from Materia

-- Tabla de Alumno
CREATE TABLE Alumno (
    id_alumno INT PRIMARY KEY IDENTITY(1,1),
    nombre VARCHAR(50) NOT NULL,
    apellido_mat VARCHAR(50) NOT NULL,
	apellido_pat VARCHAR(50) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    id_grupo INT NOT NULL,
    FOREIGN KEY (id_grupo) REFERENCES Grupo(id_grupo)
)
Select*from Alumno

--Tabla de AsignacionMateria
CREATE TABLE AsignacionMateria (
    id_asigma INT PRIMARY KEY IDENTITY(1,1),
    id_alumno INT NOT NULL,
    id_materia INT NOT NULL,
    calificacion DECIMAL(5,2) NOT NULL,
    faltas INT,
    unidad INT NOT NULL,
    id_estado INT NOT NULL,
    FOREIGN KEY (id_alumno) REFERENCES Alumno(id_alumno),
    FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    FOREIGN KEY (id_estado) REFERENCES EstadoAlumno(id_estado)
)


-- Tabla de Informe
CREATE TABLE Informe (
    id_informe INT PRIMARY KEY IDENTITY(1,1),
    id_alumno INT NOT NULL,
    id_materia INT NOT NULL,
    id_usuario INT NOT NULL, -- Quién generó el informe
    fecha DATE NOT NULL,
    observaciones text NOT NULL,
    enviado BIT NOT NULL,
    FOREIGN KEY (id_alumno) REFERENCES Alumno(id_alumno),
    FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
)