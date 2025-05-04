use DE_PEZ
go

insert Alumno values ('Juan', 'Jimenez', 'Gomez', 'juanji289@gmail.com', '872 118 8967', 1)
insert Alumno values ('jerardo', 'Hernandez', 'Perez', 'Jera23@gmail.com', '872 145 5677', 1)
insert Alumno values ('Felipe', 'Fernandez', 'Lopez', 'FelipeFDz3@gmail.com', '872 889 2345', 2)
insert Alumno values ('Taniz', 'Perez', 'Jimenez', 'elTaniz@gmail.com', '872 345 8790', 1)
insert Alumno values ('Brandon', 'Benavente', 'Zavala', 'BrandonBe2@gmail.com', '871 589 4902', 2)
insert Alumno values ('Daniel', 'Zavala', 'Subirias', 'Dani239@gmail.com', '871 113 7865', 2)
insert Alumno values ('Kevin', 'Avila', 'Ramirez', 'KevinAvi89@gmail.com', '872 895 3421', 1)
insert Alumno values ('Dresler', 'Aldair', 'Medina', 'Dresler67@gmail.com', '871 339 1390', 1)
insert Alumno values ('jesus', 'Martinez', 'Navarrete', 'Jesus2@gmail.com', '872 354 8709', 2)
insert Alumno values ('Felix', 'Lopez', 'Franco', 'Felixlop@gmail.com', '872 234 8956', 2)

INSERT INTO Materia VALUES 
('Bases de Datos', 6, 'Quinto'),
('Programación Web', 5, 'Quinto'),
('Ingeniería de Software', 5, 'Quinto'),
('Redes de Computadoras', 6, 'Cuarto'),
('Sistemas Operativos', 5, 'Cuarto'),
('Estructura de Datos', 6, 'Tercero'),
('Matemáticas Discretas', 4, 'Segundo'),
('Fundamentos de Programación', 5, 'Primero'),
('Taller de Investigación I', 4, 'Sexto'),
('Desarrollo Web Full Stack', 6, 'Sexto')

-- Profesores (14)
INSERT INTO Usuario VALUES
('Carlos', 'Martínez', 'López', 'carlos.martinez@itspc.edu.mx', '8711230001', 'pass001', 1),
('Ana', 'García', 'Mendoza', 'ana.garcia@itspc.edu.mx', '8711230002', 'pass002', 1),
('Luis', 'Sánchez', 'Ortega', 'luis.sanchez@itspc.edu.mx', '8711230003', 'pass003', 1),
('Patricia', 'Vega', 'Ramos', 'patricia.vega@itspc.edu.mx', '8711230004', 'pass004', 1),
('Jorge', 'Torres', 'Delgado', 'jorge.torres@itspc.edu.mx', '8711230005', 'pass005', 1),
('Gabriela', 'Núñez', 'Reyes', 'gabriela.nunez@itspc.edu.mx', '8711230006', 'pass006', 1),
('Arturo', 'López', 'Morales', 'arturo.lopez@itspc.edu.mx', '8711230007', 'pass007', 1),
('Sandra', 'Hernández', 'Salas', 'sandra.hernandez@itspc.edu.mx', '8711230008', 'pass008', 1),
('Fernando', 'Jiménez', 'Gómez', 'fernando.jimenez@itspc.edu.mx', '8711230009', 'pass009', 1),
('Laura', 'Pérez', 'Cruz', 'laura.perez@itspc.edu.mx', '8711230010', 'pass010', 1),
('Manuel', 'Estrada', 'Vargas', 'manuel.estrada@itspc.edu.mx', '8711230011', 'pass011', 1),
('Raquel', 'Zúñiga', 'Romero', 'raquel.zuniga@itspc.edu.mx', '8711230012', 'pass012', 1),
('Iván', 'Campos', 'Aguilar', 'ivan.campos@itspc.edu.mx', '8711230013', 'pass013', 1),
('Beatriz', 'Guerra', 'Navarro', 'beatriz.guerra@itspc.edu.mx', '8711230014', 'pass014', 1)

-- Tutores (8)
INSERT INTO Usuario VALUES
('Laura', 'Ramírez', 'Gómez', 'laura.ramirez@itspc.edu.mx', '8715678901', 'pass654', 2),
('Miguel', 'Navarro', 'Silva', 'miguel.navarro@itspc.edu.mx', '8716789012', 'pass987', 2),
('Diana', 'Cortés', 'Jiménez', 'diana.cortes@itspc.edu.mx', '8717890123', 'pass000', 2),
('Óscar', 'Ruiz', 'Flores', 'oscar.ruiz@itspc.edu.mx', '8718901234', 'pass111', 2)


-- Jefe de Carrera (1)
insert Usuario values ('Pablo Ulises','Jimenez', 'Perez', 'PabloUli@gmail.com','871 773 8975', '120211234', 3)

INSERT INTO Grupo VALUES 
('C'),
('D')


--Asignacion Materia por alumno
INSERT INTO AsignacionMateria VALUES (11, 8, 85.0, 0, 1, 1)
INSERT INTO AsignacionMateria VALUES (11, 7, 80.0, 0, 1, 1)
INSERT INTO AsignacionMateria VALUES (11, 6, 78.0, 1, 1, 2)
INSERT INTO AsignacionMateria VALUES (12, 8, 82.0, 0, 1, 1)
INSERT INTO AsignacionMateria VALUES (12, 7, 75.0, 0, 1, 1)
INSERT INTO AsignacionMateria VALUES (12, 6, 69.0, 1, 1, 2)
INSERT INTO AsignacionMateria VALUES (13, 7, 90.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (13, 6, 88.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (13, 5, 84.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (14, 7, 78.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (14, 6, 80.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (14, 5, 74.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (15, 6, 89.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (15, 5, 83.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (15, 4, 77.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (16, 6, 91.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (16, 5, 79.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (16, 4, 68.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (17, 5, 85.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (17, 4, 80.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (17, 3, 75.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (18, 5, 82.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (18, 4, 76.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (18, 3, 79.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (19, 3, 88.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (19, 2, 85.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (19, 1, 83.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (20, 10, 87.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (20, 9, 82.0, 1, 1, 1)
INSERT INTO AsignacionMateria VALUES (20, 3, 79.0, 1, 1, 1)


SELECT * FROM Rol
SELECT * FROM Usuario
SELECT * FROM Alumno
SELECT * FROM Grupo
SELECT * FROM Materia
SELECT * FROM EstadoAlumno
SELECT * FROM AsignacionMateria
SELECT * FROM Informe