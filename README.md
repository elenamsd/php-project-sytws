# Gestor de noticias (XAMPP)

Proyecto PHP para XAMPP que permite buscar noticias y, una vez logueado, crear, editar y eliminar noticias.

## Descripción
Es una pequeña aplicación web para administrar noticias. Soporta:
- Búsqueda de noticias.
- Registro/Inicio de sesión de usuarios (login required para acciones CRUD).
- Crear, editar y eliminar noticias por usuarios autenticados.

## Requisitos
- XAMPP (Apache + MySQL + PHP)
- Navegador moderno
- PHP 7.4+ (o versión compatible con el proyecto)
- Base de datos MySQL/MariaDB

## Instalación rápida
1. Copiar el proyecto a la carpeta de htdocs:
   - /Applications/XAMPP/xamppfiles/htdocs/php-project-sytws
2. Arrancar Apache y MySQL desde el panel de XAMPP.
3. Crear la base de datos (ejemplo):
   - Nombre sugerido: `noticias_db`
4. Importar el SQL de esquema si existe (por ejemplo `schema.sql`) desde phpMyAdmin o línea de comandos.
5. Configurar la conexión a la base de datos:
   - Actualizar el archivo de configuración del proyecto (por ejemplo `config.php`, `.env` o similar) con las credenciales de MySQL.
6. Acceder desde el navegador:
   - http://localhost/php-project-sytws/


