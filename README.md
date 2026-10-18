# Práctica con Docker: PHP, Apache y MySQL (ULSA)

Este proyecto implementa una aplicación web en PHP con persistencia en base de datos MySQL, orquestada mediante Docker Compose según los requerimientos de la asignatura **Tecnología de la Información** de la **Universidad Tecnológica La Salle (ULSA)**.

## 🚀 Arquitectura del Sistema

```text
Navegador (Usuario)
       |
       |  http://localhost:8080
       v
Servicio Web: PHP 8.3 + Apache
       |
       |  Red interna Docker (db:3306)
       v
Servicio de Base de Datos: MySQL 8.4
       |
       v
Volumen Persistente: mysql_data
```

## 📁 Estructura del Proyecto

```text
docker-php-mysql/
├── Dockerfile           # Imagen personalizada PHP 8.3 con Apache y pdo_mysql
├── docker-compose.yml   # Definición de servicios (web y db) y volumen persistente
├── .dockerignore        # Archivos excluidos del contexto de construcción
├── init.sql             # Script SQL de creación de la BD 'tienda', tabla 'productos' y datos iniciales
├── README.md            # Documentación del proyecto
└── src/
    ├── db.php           # Conexión a la base de datos usando PDO y variables de entorno
    └── index.php        # Aplicación web con CRUD de productos y filtros
```

## 🛠️ Tecnologías Utilizadas

- **PHP 8.3** con Apache
- **MySQL 8.4**
- **Docker & Docker Compose**
- **PDO** para consultas SQL parametrizadas

## ⚙️ Instrucciones de Ejecución

1. Abrir una terminal en la raíz del proyecto.
2. Validar la configuración del archivo compose:
   ```bash
   docker compose config
   ```
3. Construir la imagen y levantar los servicios en segundo plano:
   ```bash
   docker compose up -d --build
   ```
4. Verificar el estado de los contenedores:
   ```bash
   docker compose ps
   ```
5. Abrir en el navegador:
   ```text
   http://localhost:8080
   ```

## 🧪 Pruebas Realizadas

- [x] Consulta y despliegue de los 3 productos iniciales definidos en `init.sql`.
- [x] Registro/inserción de nuevos productos con validación y consultas preparadas.
- [x] Eliminación de productos mediante POST.
- [x] **Reto A:** Modificación / edición de nombre y precio de productos existentes.
- [x] **Reto B:** Búsqueda en tiempo real por coincidencia (`LIKE :termino`) parametrizada.
- [x] **Persistencia comprobada:** Detención con `docker compose down` y reinicio con `docker compose up -d`, verificando que los datos almacenados en el volumen `mysql_data` se preservan intactos.

## 🧹 Detención y Limpieza

- Detener servicios manteniendo los datos en el volumen:
  ```bash
  docker compose down
  ```
- Detener servicios y eliminar el volumen persistente:
  ```bash
  docker compose down -v
  ```
