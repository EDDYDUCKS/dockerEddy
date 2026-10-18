CREATE DATABASE IF NOT EXISTS tienda;
USE tienda;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO productos (nombre, precio, creado_en) VALUES
('Monitor de 24 pulgadas', 175.00, '2026-10-02 03:52:23'),
('Pantalla', 34.00, '2026-10-02 03:53:12'),
('Monitor', 45.00, '2026-10-05 19:46:54');
