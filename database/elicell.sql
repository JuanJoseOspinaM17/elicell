-- ============================================================
-- BASE DE DATOS: elicell
-- Sistema de registro de reparaciones y garantías
-- ============================================================

CREATE DATABASE IF NOT EXISTS elicell
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE elicell;


-- ============================================================
-- TABLA: admins
-- Usuarios que pueden acceder al panel administrativo
-- ============================================================

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- TABLA: estados
-- Estados disponibles para los servicios
-- ============================================================

CREATE TABLE IF NOT EXISTS estados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
);


-- ============================================================
-- INSERTAR ESTADOS INICIALES
-- ============================================================

INSERT INTO estados (nombre, descripcion)
VALUES
    ('Recibido', 'El equipo fue recibido y está pendiente de revisión.'),
    ('En revisión', 'El equipo está siendo revisado.'),
    ('En reparación', 'El equipo se encuentra en proceso de reparación.'),
    ('Reparado', 'La reparación fue terminada.'),
    ('Entregado', 'El equipo fue entregado al cliente.'),
    ('No reparado', 'El equipo no pudo ser reparado.'),
    ('Cancelado', 'El servicio fue cancelado.')
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion);


-- ============================================================
-- TABLA: garantias
-- Registro completo de cada reparación
-- ============================================================

CREATE TABLE IF NOT EXISTS garantias (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- --------------------------------------------------------
    -- IDENTIFICACIÓN DE LA GARANTÍA
    -- --------------------------------------------------------

    numero_garantia VARCHAR(30) NOT NULL UNIQUE,


    -- --------------------------------------------------------
    -- DATOS DEL CLIENTE
    -- --------------------------------------------------------

    nombre_cliente VARCHAR(150) NOT NULL,

    telefono_cliente VARCHAR(30) NOT NULL,


    -- --------------------------------------------------------
    -- DATOS DEL EQUIPO
    -- --------------------------------------------------------

    tipo_dispositivo VARCHAR(50) NOT NULL,

    marca VARCHAR(100) NOT NULL,

    modelo VARCHAR(100) NOT NULL,

    imei VARCHAR(50) DEFAULT NULL,


    -- --------------------------------------------------------
    -- INFORMACIÓN DE INGRESO
    -- --------------------------------------------------------

    falla TEXT NOT NULL,

    estado_fisico_entrada TEXT DEFAULT NULL,

    observaciones_entrada TEXT DEFAULT NULL,


    -- --------------------------------------------------------
    -- INFORMACIÓN DE LA REPARACIÓN
    -- --------------------------------------------------------

    trabajo_realizado TEXT DEFAULT NULL,

    repuestos TEXT DEFAULT NULL,

    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,


    -- --------------------------------------------------------
    -- GARANTÍA
    -- --------------------------------------------------------

    tiempo_garantia INT UNSIGNED NOT NULL DEFAULT 0,

    unidad_garantia ENUM(
        'dias',
        'meses',
        'anos'
    ) NOT NULL DEFAULT 'dias',

    fecha_vencimiento_garantia DATE DEFAULT NULL,


    -- --------------------------------------------------------
    -- ENTRADA DEL EQUIPO
    -- --------------------------------------------------------

    fecha_entrada DATE NOT NULL,

    hora_entrada TIME NOT NULL,


    -- --------------------------------------------------------
    -- SALIDA DEL EQUIPO
    -- --------------------------------------------------------

    fecha_salida DATE DEFAULT NULL,

    hora_salida TIME DEFAULT NULL,


    -- --------------------------------------------------------
    -- ESTADO DEL SERVICIO
    -- --------------------------------------------------------

    estado_id INT UNSIGNED NOT NULL DEFAULT 1,


    -- --------------------------------------------------------
    -- INFORMACIÓN DE SALIDA
    -- --------------------------------------------------------

    observaciones_salida TEXT DEFAULT NULL,


    -- --------------------------------------------------------
    -- INFORMACIÓN ADMINISTRATIVA
    -- --------------------------------------------------------

    creado_por INT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    -- --------------------------------------------------------
    -- RELACIONES
    -- --------------------------------------------------------

    CONSTRAINT fk_garantias_estado
        FOREIGN KEY (estado_id)
        REFERENCES estados(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_garantias_admin
        FOREIGN KEY (creado_por)
        REFERENCES admins(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

);


-- ============================================================
-- ÍNDICES
-- Mejoran las búsquedas del panel administrativo
-- ============================================================

CREATE INDEX idx_nombre_cliente
ON garantias(nombre_cliente);

CREATE INDEX idx_telefono_cliente
ON garantias(telefono_cliente);

CREATE INDEX idx_imei
ON garantias(imei);

CREATE INDEX idx_marca
ON garantias(marca);

CREATE INDEX idx_modelo
ON garantias(modelo);

CREATE INDEX idx_estado
ON garantias(estado_id);

CREATE INDEX idx_fecha_entrada
ON garantias(fecha_entrada);

CREATE INDEX idx_fecha_vencimiento
ON garantias(fecha_vencimiento_garantia);


-- ============================================================
-- ADMINISTRADOR INICIAL
-- ============================================================
-- Usuario:
-- admin
--
-- Contraseña:
-- admin123
--
-- IMPORTANTE:
-- Esta contraseña es temporal y posteriormente la cambiaremos.
-- El hash corresponde a "admin123".
-- ============================================================

INSERT INTO admins (
    nombre,
    usuario,
    password,
    activo
)
VALUES (
    'Administrador',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqYkM9f2F8h9k4h5kO2',
    1
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre);


-- ============================================================
-- FIN DE LA BASE DE DATOS
-- ============================================================