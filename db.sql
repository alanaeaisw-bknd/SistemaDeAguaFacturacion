-- ============================
-- BASE DE DATOS: sistemadeagua
-- ============================
DROP DATABASE IF EXISTS sistemadeagua;
CREATE DATABASE sistemadeagua;
USE sistemadeagua;

-- ============================
-- 1️⃣ USUARIOS
-- ============================
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- 2️⃣ DATOS FISCALES
-- ============================
CREATE TABLE datos_fiscales (
    id_datos_fiscales INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    rfc VARCHAR(13) NOT NULL,
    razon_social VARCHAR(150) NOT NULL,
    correo VARCHAR(120) NOT NULL,
    telefono VARCHAR(20),
    calle VARCHAR(150) NOT NULL,
    numero_ext VARCHAR(10) NOT NULL,
    numero_int VARCHAR(10),
    colonia VARCHAR(100) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    pais VARCHAR(100) DEFAULT 'México',
    cp VARCHAR(10) NOT NULL,
    regimen VARCHAR(100) NOT NULL,
    uso_cfdi VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- ============================
-- 3️⃣ PRODUCTOS (Solo 3 fijos)
-- ============================
CREATE TABLE productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(150),
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE
);

-- Insertar los 3 productos fijos
INSERT INTO productos (nombre, descripcion, precio, stock, activo) VALUES
('Botella 500ml', 'Botella de agua purificada 500ml', 8.00, 1000, TRUE),
('Galón 3.78L', 'Galón de agua purificada 3.78 litros', 25.00, 500, TRUE),
('Garrafón 19L', 'Garrafón de agua purificada 19 litros', 45.00, 300, TRUE);

-- ============================
-- 4️⃣ CARRITO
-- ============================
CREATE TABLE carrito (
    id_carrito INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_carrito_usuario UNIQUE (id_usuario),
    CONSTRAINT fk_carrito_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);


-- ============================
-- 5️⃣ DETALLE CARRITO
-- ============================
CREATE TABLE detalle_carrito (
    id_detalle_carrito INT PRIMARY KEY AUTO_INCREMENT,
    id_carrito INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    UNIQUE KEY unique_carrito_producto (id_carrito, id_producto)
);

-- ============================
-- 6️⃣ VENTAS
-- ============================
CREATE TABLE ventas (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_datos_fiscales INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagada', 'cancelada') DEFAULT 'pendiente',
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_datos_fiscales) REFERENCES datos_fiscales(id_datos_fiscales)
);

-- ============================
-- 7️⃣ DETALLE VENTA
-- ============================
CREATE TABLE detalle_venta (
    id_detalle_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- ============================
-- 🔥 TRIGGERS
-- ============================

-- Trigger 1: Descuenta stock cuando se confirma venta
DELIMITER $$
CREATE TRIGGER trg_descontar_stock
AFTER INSERT ON detalle_venta
FOR EACH ROW
BEGIN
    UPDATE productos
    SET stock = stock - NEW.cantidad
    WHERE id_producto = NEW.id_producto;
END$$
DELIMITER ;

-- Trigger 2: Actualiza total del carrito al agregar/modificar items
DELIMITER $$
CREATE TRIGGER trg_actualizar_total_carrito_insert
AFTER INSERT ON detalle_carrito
FOR EACH ROW
BEGIN
    UPDATE carrito
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0)
        FROM detalle_carrito
        WHERE id_carrito = NEW.id_carrito
    )
    WHERE id_carrito = NEW.id_carrito;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_actualizar_total_carrito_update
AFTER UPDATE ON detalle_carrito
FOR EACH ROW
BEGIN
    UPDATE carrito
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0)
        FROM detalle_carrito
        WHERE id_carrito = NEW.id_carrito
    )
    WHERE id_carrito = NEW.id_carrito;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_actualizar_total_carrito_delete
AFTER DELETE ON detalle_carrito
FOR EACH ROW
BEGIN
    UPDATE carrito
    SET total = (
        SELECT IFNULL(SUM(subtotal), 0)
        FROM detalle_carrito
        WHERE id_carrito = OLD.id_carrito
    )
    WHERE id_carrito = OLD.id_carrito;
END$$
DELIMITER ;

-- ============================
-- 📦 STORED PROCEDURES
-- ============================

-- SP 1: Obtener resumen de ventas por usuario
DELIMITER $$
CREATE PROCEDURE sp_resumen_ventas_usuario(IN p_id_usuario INT)
BEGIN
    SELECT 
        v.id_venta,
        v.total,
        v.estado,
        v.fecha_venta,
        df.razon_social,
        df.rfc,
        COUNT(dv.id_detalle_venta) as total_items
    FROM ventas v
    INNER JOIN datos_fiscales df ON v.id_datos_fiscales = df.id_datos_fiscales
    LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
    WHERE v.id_usuario = p_id_usuario
    GROUP BY v.id_venta
    ORDER BY v.fecha_venta DESC;
END$$
DELIMITER ;

-- SP 2: Validar stock disponible antes de venta
DELIMITER $$
CREATE PROCEDURE sp_validar_stock_carrito(IN p_id_carrito INT)
BEGIN
    SELECT 
        dc.id_producto,
        p.nombre,
        dc.cantidad as cantidad_solicitada,
        p.stock as stock_disponible,
        CASE 
            WHEN dc.cantidad > p.stock THEN 'INSUFICIENTE'
            ELSE 'SUFICIENTE'
        END as estado_stock
    FROM detalle_carrito dc
    INNER JOIN productos p ON dc.id_producto = p.id_producto
    WHERE dc.id_carrito = p_id_carrito;
END$$
DELIMITER ;

-- SP 3: Obtener productos con stock bajo (menos de 100 unidades)
DELIMITER $$
CREATE PROCEDURE sp_productos_stock_bajo()
BEGIN
    SELECT 
        id_producto,
        nombre,
        stock,
        CASE 
            WHEN stock = 0 THEN 'SIN STOCK'
            WHEN stock < 50 THEN 'CRÍTICO'
            WHEN stock < 100 THEN 'BAJO'
        END as nivel_alerta
    FROM productos
    WHERE stock < 100 AND activo = TRUE
    ORDER BY stock ASC;
END$$
DELIMITER ;