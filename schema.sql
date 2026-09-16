CREATE DATABASE IF NOT EXISTS escuela CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE esccuela;

-- Tabla de Usuarios (Administración, Dirección, Coordinación, Docentes)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'direccion', 'coordinacion', 'docente') NOT NULL,
    grado_asignado ENUM('Inicial 2', 'Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo') NULL
);

-- Insertar usuario Admin por defecto (Password: admin123)
INSERT INTO usuarios (cedula, nombre_completo, correo, password, rol) 
VALUES ('0000000000', 'Administrador General', 'admin@escuela.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1fWbZ4U6Y78d8o4K4l.D8eG/d7VlS0W', 'administrador')
ON DUPLICATE KEY UPDATE id=id;

-- Tabla de Estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT 'default.png',
    grado ENUM('Inicial 2', 'Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    pais_nacimiento VARCHAR(50) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    domicilio TEXT NOT NULL,
    nombre_padre VARCHAR(100),
    cedula_padre VARCHAR(20),
    telefono_padre VARCHAR(20),
    nombre_madre VARCHAR(100),
    cedula_madre VARCHAR(20),
    telefono_madre VARCHAR(20),
    autorizados_retirar TEXT
);

-- Tabla de Calificaciones
CREATE TABLE IF NOT EXISTS calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    docente_id INT NOT NULL,
    archivo_pdf VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);