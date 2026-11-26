# Labor social escolar

Sistema para la Institución Educativa XYZ. Permite administrar estudiantes, docentes y proyectos, registrar actividades y consultar un informe individual con la meta de 80 horas.

## Arranque rápido con Docker

1. Abre Docker Desktop o inicia el servicio de Docker. Desde la carpeta que contiene `compose.yaml`, ejecuta:

   ```sh
   docker compose up --build -d --wait
   ```

2. En tu navegador local, abre `http://localhost:3000`. Si el puerto ya está ocupado, crea un archivo `.env` junto a `compose.yaml` con `LABOR_WEB_PORT=3001`, vuelve a ejecutar el comando y usa ese puerto en el navegador.
3. En **Informes**, busca `Valentina Rojas` y abre **Ver informe**. Los datos ficticios iniciales dan 10 actividades y **80 / 80 h**.
4. Para cerrar los servicios sin perder tus cambios, usa `docker compose down`. Al volver a iniciarlos, la base conserva los cambios en el volumen `datos`. Los SQL de ejemplo se importan solo al crear ese volumen por primera vez.

## Alternativa en Windows con XAMPP

1. Descomprime el proyecto en una carpeta de trabajo. En el panel de XAMPP inicia **MySQL** y **Apache** para usar phpMyAdmin.
2. En tu navegador abre `http://localhost/phpmyadmin`. En la pantalla inicial elige **Importar** y selecciona `database/01-schema.sql`. Después importa `database/02-demo.sql` una sola vez para cargar el ejemplo. El esquema crea la base `labor_social`.
3. Abre PowerShell en la carpeta del proyecto y ejecuta:

   ```powershell
   & 'C:\xampp\php\php.exe' -S 127.0.0.1:3000 -t public
   ```

4. Abre `http://localhost:3000` y comprueba el informe de Valentina. Puedes cerrar PHP con **Ctrl+C**.

La aplicación requiere PHP 8.1 o superior con PDO MySQL e iconv, y MariaDB 10.4 o MySQL 8.0.16 en adelante. Si tu instalación local usa otro puerto, usuario o contraseña, crea `app/local.php` con las claves que necesitas cambiar:

```php
<?php
return [
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '',
];
```

Completa ese archivo con tus valores locales y exclúyelo del ZIP. Usa el sitio de demostración con datos ficticios en tu equipo.

## Archivos para el parcial

| Archivo | Contenido |
| --- | --- |
| [database/01-schema.sql](database/01-schema.sql) | Base de datos, tablas y restricciones. |
| [database/02-demo.sql](database/02-demo.sql) | Datos ficticios para la demostración. |
| [database/modelo-er.png](database/modelo-er.png) | Imagen del diagrama entidad–relación. |
| [public/](public/) y [app/](app/) | Archivos PHP, HTML, CSS y JavaScript. |
| [app/config.php](app/config.php) | Tu nombre completo, programa académico y cinco enlaces que aparecen en Inicio. |
| [Guía de grabación](docs/guion-videos.md) | Pasos, código que debes mostrar y guion para cada video. |
| [PDF rellenable](docs/Parcial3_basededatos-rellenable.pdf) | Enunciado original con cinco campos para los enlaces en la última página. |

## Reglas de los datos

- Un estudiante de grado 10° u 11° elige un proyecto. En este modelo el informe muestra el proyecto actualmente asignado al estudiante.
- Cada actividad registra una fecha, horas positivas con hasta dos decimales, trabajo realizado y un docente responsable.
- El informe ordena las actividades por fecha e ID y calcula el acumulado con `SUM(...) OVER (...)`. Muestra el total y cuántas horas faltan. La aplicación admite exactamente 80 y rechaza registros o ediciones que dejen el total por encima de 80.
- Las llaves foráneas protegen estudiantes, docentes y proyectos con referencias. Para el video de borrado se incluye un proyecto sin estudiantes.

Antes de entregar, completa `app/config.php` y el PDF, comprueba los enlaces en una ventana privada y vuelve a comprimir la carpeta del proyecto. Excluye `.git`, `.env`, `app/local.php` y cualquier archivo con contraseñas o datos personales.
