CREATE DATABASE IF NOT EXISTS mediconnect
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mediconnect;

-- ------------------------------------------------------------
-- 1. USUARIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_completo  VARCHAR(150)  NOT NULL,
    email            VARCHAR(100)  NOT NULL UNIQUE,
    password         VARCHAR(255)  NOT NULL,
    rol              ENUM('superadmin','admin_sede','prestador','cliente') NOT NULL DEFAULT 'cliente',
    fecha_registro   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. SEDES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sedes (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_sede        VARCHAR(100)  NOT NULL,
    direccion          VARCHAR(255)  NOT NULL,
    ciudad             VARCHAR(100)  NOT NULL,
    telefono_contacto  VARCHAR(20)   DEFAULT NULL,
    estado             ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. ADMINS_SEDES  (relación usuario-sede para rol admin_sede)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins_sedes (
    usuario_id  INT UNSIGNED NOT NULL,
    sede_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (usuario_id, sede_id),
    CONSTRAINT fk_as_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_as_sede    FOREIGN KEY (sede_id)
        REFERENCES sedes(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. SERVICIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servicios (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_servicio  VARCHAR(100)    NOT NULL,
    descripcion      TEXT            DEFAULT NULL,
    precio           DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    duracion_minutos INT UNSIGNED    NOT NULL DEFAULT 30
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. MEDICO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medico (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT UNSIGNED  NOT NULL,
    sede_id             INT UNSIGNED  NOT NULL,
    cargo_especialidad  VARCHAR(100)  NOT NULL,
    perfil_profesional  TEXT          DEFAULT NULL,
    CONSTRAINT fk_med_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_med_sede    FOREIGN KEY (sede_id)
        REFERENCES sedes(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. DISPONIBILIDAD
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS disponibilidad (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medico_id   INT UNSIGNED NOT NULL,
    dia_semana  ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin    TIME NOT NULL,
    CONSTRAINT fk_disp_medico FOREIGN KEY (medico_id)
        REFERENCES medico(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. CITAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citas (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id   INT UNSIGNED NOT NULL,
    medico_id    INT UNSIGNED NOT NULL,
    servicio_id  INT UNSIGNED NOT NULL,
    sede_id      INT UNSIGNED NOT NULL,
    fecha_cita   DATE         NOT NULL,
    hora_cita    TIME         NOT NULL,
    estado       ENUM('pendiente','confirmada','cancelada','finalizada') NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_cita_cliente  FOREIGN KEY (cliente_id)
        REFERENCES usuarios(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_cita_medico   FOREIGN KEY (medico_id)
        REFERENCES medico(id)    ON DELETE RESTRICT,
    CONSTRAINT fk_cita_servicio FOREIGN KEY (servicio_id)
        REFERENCES servicios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cita_sede     FOREIGN KEY (sede_id)
        REFERENCES sedes(id)     ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. LOGS_ATENCION
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_atencion (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cita_id              INT UNSIGNED NOT NULL,
    observaciones_finales TEXT        DEFAULT NULL,
    tratamiento_o_resultado TEXT      DEFAULT NULL,
    proxima_cita_sugerida DATE        DEFAULT NULL,
    CONSTRAINT fk_log_cita FOREIGN KEY (cita_id)
        REFERENCES citas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DATOS INICIALES – superadmin
-- password: Admin1234! (hash bcrypt)
-- ------------------------------------------------------------
INSERT INTO usuarios (nombre_completo, email, password, rol)
VALUES (
    'Super Administrador',
    'admin@mediconnect.test',
    '$2y$12$placeholder_hash_change_this',
    'superadmin'
);