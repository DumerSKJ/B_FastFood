-- CONVERSIÓN DE MYSQL A POSTGRESQL 17
-- Sistema: MutiSistem (Login y Módulos)

-- Configuraciones iniciales
SET statement_timeout = 0;

SET lock_timeout = 0;

SET client_encoding = 'UTF8';

SET standard_conforming_strings = on;

SET check_function_bodies = false;

SET xmloption = content;

SET client_min_messages = warning;

SET row_security = off;

-- --------------------------------------------------------
-- 1. Tabla: modulos
-- --------------------------------------------------------
CREATE TABLE modulos (
    idmodulo SERIAL PRIMARY KEY,
    nombremodulo VARCHAR(100) NOT NULL,
    folder_name VARCHAR(100),
    icono VARCHAR(50) DEFAULT 'fas fa-cube',
    orden INT DEFAULT 0
);

INSERT INTO
    modulos (
        idmodulo,
        nombremodulo,
        folder_name,
        icono,
        orden
    )
VALUES (
        1,
        'Configuraciones',
        'Configuracion',
        'fas fa-sliders',
        2
    );

-- Sincronizar secuencia del autoincremento
SELECT pg_catalog.setval (
        'modulos_idmodulo_seq', (
            SELECT MAX(idmodulo)
            FROM modulos
        ), true
    );

-- --------------------------------------------------------
-- 2. Tabla: sub_modulos
-- --------------------------------------------------------
CREATE TABLE sub_modulos (
    idsubmodulo SERIAL PRIMARY KEY,
    idsubmodulopadre INT DEFAULT NULL,
    nombresubmodulo VARCHAR(100) NOT NULL,
    folder_name VARCHAR(100),
    view_key VARCHAR(50) NOT NULL,
    icono VARCHAR(50) DEFAULT 'fas fa-circle',
    acceso_rapido BOOLEAN NOT NULL DEFAULT FALSE,
    seccion_atajo VARCHAR(100) DEFAULT NULL,
    imagen_atajo VARCHAR(100) DEFAULT NULL,
    orden_seccion_atajo INT NOT NULL DEFAULT 0,
    orden_atajo INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_submodulo_padre FOREIGN KEY (idsubmodulopadre) REFERENCES sub_modulos (idsubmodulo) ON DELETE SET NULL
);

INSERT INTO
    sub_modulos (
        idsubmodulo,
        idsubmodulopadre,
        nombresubmodulo,
        folder_name,
        view_key,
        icono,
        acceso_rapido,
        seccion_atajo,
        imagen_atajo,
        orden_seccion_atajo,
        orden_atajo
    )
VALUES (
        1,
        NULL,
        'Usuarios',
        'Usuarios',
        'usuarios',
        'fas fa-key',
        FALSE,
        '',
        NULL,
        0,
        0
    ),
    (
        2,
        NULL,
        'Personal',
        'Personal',
        'personal',
        'fas fa-address-book',
        FALSE,
        NULL,
        NULL,
        0,
        0
    ),
    (
        3,
        NULL,
        'Roles',
        'Rol',
        'roles',
        'fas fa-id-badge',
        FALSE,
        NULL,
        NULL,
        0,
        0
    ),
    (
        4,
        NULL,
        'Gestión de Menú',
        'Menu',
        'menu',
        'fas fa-list-check',
        FALSE,
        NULL,
        NULL,
        0,
        0
    );

SELECT pg_catalog.setval (
        'sub_modulos_idsubmodulo_seq', (
            SELECT MAX(idsubmodulo)
            FROM sub_modulos
        ), true
    );

-- --------------------------------------------------------
-- 3. Tabla: modulo_sub_modulo
-- --------------------------------------------------------
CREATE TABLE modulo_sub_modulo (
    idmodulosubmodulo SERIAL PRIMARY KEY,
    idmodulofk INT NOT NULL,
    idsubmodulofk INT NOT NULL,
    orden INT DEFAULT 0,
    CONSTRAINT fk_rel_modulo FOREIGN KEY (idmodulofk) REFERENCES modulos (idmodulo) ON DELETE CASCADE,
    CONSTRAINT fk_rel_submodulo FOREIGN KEY (idsubmodulofk) REFERENCES sub_modulos (idsubmodulo) ON DELETE CASCADE,
    CONSTRAINT idx_modulo_sub UNIQUE (idmodulofk, idsubmodulofk)
);

INSERT INTO
    modulo_sub_modulo (
        idmodulosubmodulo,
        idmodulofk,
        idsubmodulofk,
        orden
    )
VALUES (1, 1, 1, 1),
    (2, 1, 2, 2),
    (3, 1, 3, 3),
    (4, 1, 4, 4);

SELECT pg_catalog.setval (
        'modulo_sub_modulo_idmodulosubmodulo_seq', (
            SELECT MAX(idmodulosubmodulo)
            FROM modulo_sub_modulo
        ), true
    );

-- --------------------------------------------------------
-- 4. Tabla: personal
-- --------------------------------------------------------
CREATE TABLE personal (
    idpersonal SERIAL PRIMARY KEY,
    dni VARCHAR(8) UNIQUE DEFAULT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) DEFAULT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    fecharegistro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    personal (
        idpersonal,
        dni,
        nombres,
        apellidos,
        correo,
        telefono,
        fecharegistro
    )
VALUES (
        1,
        NULL,
        'Desarrollador',
        'General',
        'dev@sistema.com',
        NULL,
        '2025-12-27 16:39:12'
    ),
    (
        4,
        '43390175',
        'JESSICA JANET',
        'CAMPOS SALINAS',
        '',
        '-',
        '2026-01-30 15:47:20'
    );

SELECT pg_catalog.setval (
        'personal_idpersonal_seq', (
            SELECT MAX(idpersonal)
            FROM personal
        ), true
    );

-- --------------------------------------------------------
-- 5. Tabla: roles
-- --------------------------------------------------------
CREATE TABLE roles (
    idrol SERIAL PRIMARY KEY,
    nombrerol VARCHAR(50) NOT NULL,
    rolsubmodulo JSONB DEFAULT NULL, -- Convertido a JSONB para mejor manejo en PostgreSQL
    fecharegistro TIMESTAMP NOT NULL DEFAULT '2025-12-26 22:06:35'
);

INSERT INTO
    roles (
        idrol,
        nombrerol,
        rolsubmodulo,
        fecharegistro
    )
VALUES (
        1,
        'Desarrollador',
        '[1,2,3,4]',
        '2025-12-26 22:06:35'
    ),
    (
        2,
        'Super Admin',
        '[]',
        '2025-12-26 22:06:35'
    ),
    (
        3,
        'Usuario',
        '[4]',
        '2025-12-26 22:06:35'
    );

SELECT pg_catalog.setval (
        'roles_idrol_seq', (
            SELECT MAX(idrol)
            FROM roles
        ), true
    );

-- --------------------------------------------------------
-- 6. Tabla: usuarios
-- --------------------------------------------------------
-- NOTA: Se asume que existe la tabla 'establecimientos'. Si no existe, esta FK fallará.
CREATE TABLE usuarios (
    idusuario SERIAL PRIMARY KEY,
    idrolfk INT NOT NULL,
    idpersonalfk INT NOT NULL,
    idestablecimientosfk INT DEFAULT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Activo',
    fecharegistro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rol FOREIGN KEY (idrolfk) REFERENCES roles (idrol) ON DELETE RESTRICT,
    CONSTRAINT fk_personal FOREIGN KEY (idpersonalfk) REFERENCES personal (idpersonal) ON DELETE RESTRICT
    -- CONSTRAINT fk_usuarios_establecimientos1 FOREIGN KEY (idestablecimientosfk) REFERENCES establecimientos (idestablecimientos)
);

INSERT INTO
    usuarios (
        idusuario,
        idrolfk,
        idpersonalfk,
        idestablecimientosfk,
        usuario,
        clave,
        estado,
        fecharegistro
    )
VALUES (
        1,
        1,
        1,
        1,
        'dev',
        '$2y$10$dQjjo8Pw2.wM1sxydSS51uzfLnRvkddrz0DZFFs7jicob.5xvqRJa',
        'Activo',
        '2025-12-27 16:39:12'
    );

SELECT pg_catalog.setval (
        'usuarios_idusuario_seq', (
            SELECT MAX(idusuario)
            FROM usuarios
        ), true
    );

-- --------------------------------------------------------
-- 7. Tabla: usuario_permisos
-- --------------------------------------------------------
CREATE TABLE usuario_permisos (
    idpermiso SERIAL PRIMARY KEY,
    idmodulosubmodulofk INT NOT NULL,
    idusuariofk INT NOT NULL,
    acceso BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_permiso_relacion FOREIGN KEY (idmodulosubmodulofk) REFERENCES modulo_sub_modulo (idmodulosubmodulo) ON DELETE CASCADE,
    CONSTRAINT fk_permiso_usuario FOREIGN KEY (idusuariofk) REFERENCES usuarios (idusuario) ON DELETE CASCADE,
    CONSTRAINT idx_user_rel UNIQUE (
        idmodulosubmodulofk,
        idusuariofk
    )
);

-- (Sin datos en el volcado original)

SELECT pg_catalog.setval (
        'usuario_permisos_idpermiso_seq', (
            SELECT COALESCE(MAX(idpermiso), 0) + 1
            FROM usuario_permisos
        ), false
    );