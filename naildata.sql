-- =========================================================
-- BASE DE DATOS: NAILDATA
-- Motor: MySQL
-- =========================================================

CREATE DATABASE IF NOT EXISTS naildata
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE naildata;

SET FOREIGN_KEY_CHECKS = 0;

-- Borrar tablas si ya existen para empezar desde cero
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS servicio;
DROP TABLE IF EXISTS alergia_clienta;
DROP TABLE IF EXISTS alergia;
DROP TABLE IF EXISTS clientas;
DROP TABLE IF EXISTS usuario;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- 1. TABLA USUARIO
-- Controla el acceso al sistema (administradora o clienta)
-- =========================================================

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM ('administradora', 'clienta') NOT NULL
) ENGINE=InnoDB;

-- =========================================================
-- 2. TABLA CLIENTAS
-- Guarda los datos básicos de las clientas y preferencias
-- =========================================================

CREATE TABLE clientas (
    id_clienta INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    preferencias TEXT,

    CONSTRAINT fk_clientas_usuario
        FOREIGN KEY (id_usuario) 
        REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 3. TABLA ALERGIA
-- Catálogo de posibles alergias
-- =========================================================

CREATE TABLE alergia (
    id_alergia INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(60) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- =========================================================
-- 4. TABLA ALERGIA_CLIENTA
-- Relaciona a las clientas con sus respectivas alergias (N:M)
-- =========================================================

CREATE TABLE alergia_clienta (
    id_clienta INT NOT NULL,
    id_alergia INT NOT NULL,
    PRIMARY KEY (id_clienta, id_alergia),

    CONSTRAINT fk_ac_clienta
        FOREIGN KEY (id_clienta) 
        REFERENCES clientas(id_clienta)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ac_alergia
        FOREIGN KEY (id_alergia) 
        REFERENCES alergia(id_alergia)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 5. TABLA SERVICIO
-- Catálogo de servicios ofrecidos, duración y precio
-- =========================================================

CREATE TABLE servicio (
    id_servicio INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(80) NOT NULL,
    tipo VARCHAR(40),
    duracion_min INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- =========================================================
-- 6. TABLA CITAS
-- Registra las citas programadas por los usuarios
-- =========================================================

CREATE TABLE citas (
    id_cita INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_servicio INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM ('pendiente', 'atendida', 'cancelada', 'no_asistio') NOT NULL DEFAULT 'pendiente',

    CONSTRAINT fk_citas_usuario
        FOREIGN KEY (id_usuario) 
        REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_citas_servicio
        FOREIGN KEY (id_servicio) 
        REFERENCES servicio(id_servicio)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 7. TABLA PAGOS
-- Registra los pagos realizados por cita
-- =========================================================

CREATE TABLE pagos (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    id_cita INT UNIQUE NOT NULL,
    id_usuario INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,

    CONSTRAINT fk_pagos_cita
        FOREIGN KEY (id_cita) 
        REFERENCES citas(id_cita)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pagos_usuario
        FOREIGN KEY (id_usuario) 
        REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- INSERTAR DATOS DE PRUEBA
-- =========================================================

-- USUARIOS

INSERT INTO usuario (id_usuario, usuario, contrasena, rol) VALUES
(1,  'admin',      'admin123',   'administradora'),
(2,  '3104567821', '3104567821', 'clienta'),
(3,  '3157893345', '3157893345', 'clienta'),
(4,  '3202345567', '3202345567', 'clienta'),
(5,  '3118890012', '3118890012', 'clienta'),
(6,  '3009876543', '3009876543', 'clienta'),
(7,  '3173456789', '3173456789', 'clienta'),
(8,  '3226781234', '3226781234', 'clienta'),
(9,  '3145559090', '3145559090', 'clienta'),
(10, '3012223344', '3012223344', 'clienta'),
(11, '3189997766', '3189997766', 'clienta');

-- CLIENTAS

INSERT INTO clientas (id_usuario, nombre, telefono, preferencias) VALUES
(2,  'Valentina Ríos',  '3104567821', 'Uñas cortas y tonos nude'),
(3,  'Camila Gómez',    '3157893345', 'Diseños con flores'),
(4,  'Daniela Torres',  '3202345567', 'Acrílico largo en forma almendra'),
(5,  'Laura Mejía',     '3118890012', 'Semipermanente en colores oscuros'),
(6,  'Sofía Herrera',   '3009876543', 'Francesa clásica'),
(7,  'Mariana López',   '3173456789', 'Nail art con brillos'),
(8,  'Isabela Cano',    '3226781234', 'Colores pastel'),
(9,  'Juliana Ospina',  '3145559090', 'Uñas cuadradas con esmalte rojo'),
(10, 'Paula Vargas',    '3012223344', 'Sin preferencias definidas'),
(11, 'Andrea Salazar',  '3189997766', 'Gel natural y diseño minimalista');

-- SERIVICIOS
INSERT INTO servicio (nombre, tipo, duracion_min, precio) VALUES
('Uñas Acrílicas', 'Manicure', 120, 60000.00),
('Esmaltado Semipermanente', 'Manicure', 60, 35000.00),
('Pedicure Spa Clásico', 'Pedicure', 90, 45000.00),
('Retoque de Acrílico', 'Manicure', 90, 40000.00),
('Jelly Spa Completo', 'Pedicure', 60, 50000.00);


-- =========================================================
-- USUARIOS Y CONTRASEÑAS PARA PROBAR EL LOGIN
-- =========================================================
-- 
-- 1. ADMINISTRADORA
--    Usuario: admin
--    Contraseña: admin123
--
-- 2. CLIENTAS (El usuario y la contraseña son su número)
--    Usuario: 3104567821 | Contraseña: 3104567821 (Valentina Ríos)
--    Usuario: 3157893345 | Contraseña: 3157893345 (Camila Gómez)
--    Usuario: 3202345567 | Contraseña: 3202345567 (Daniela Torres)
--    Usuario: 3118890012 | Contraseña: 3118890012 (Laura Mejía)
--
-- * Nota: Las contraseñas están almacenadas en texto plano
--   para las pruebas locales, por lo que el login las lee 
--   de forma directa.
-- =========================================================


-- =========================================================
-- CONSULTAS PARA VERIFICAR LOS DATOS 
-- =========================================================

SELECT * FROM usuario;
SELECT * FROM clientas;
SELECT * FROM alergia;
SELECT * FROM alergia_clienta;
SELECT * FROM servicio;
SELECT * FROM citas;
SELECT * FROM pagos;