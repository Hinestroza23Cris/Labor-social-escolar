# Guía para grabar y entregar el parcial

El PDF pide **cinco videos** con tu **webcam y la pantalla** visibles y una explicación del **código**. Los tiempos de abajo son sugeridos. Usa los guiones como referencia y explica lo que estás haciendo con tus palabras.

## Antes de grabar

1. Inicia el sitio con el [README](../README.md). Verifica que ves **Valentina Rojas**, **Mateo Salazar**, **Huerta de prueba** y el informe completo de Valentina.
2. Abre en un editor el SQL, el diagrama PNG y los PHP que vas a explicar. Ten el navegador abierto con la aplicación. Si usas Docker, deja una terminal en la carpeta del proyecto.
3. En [app/config.php](../app/config.php) escribe tu **nombre completo** y **programa académico**. Recarga Inicio y baja a **Presentación del proyecto** para confirmar que aparecen.
4. En ScreenPal elige capturar **pantalla y webcam**. En Clipchamp puedes usar la opción **Pantalla y cámara**. Selecciona el micrófono y ubica la cámara en una esquina donde no tape el botón Guardar ni la tabla.
5. Haz una prueba de diez segundos. Reproduce el archivo para comprobar tu cara, tu voz y la legibilidad. Cierra ventanas personales, silencia notificaciones y agranda el texto del editor si hace falta.
6. Usa datos ficticios. En cada clip da un saludo corto, menciona tu nombre y muestra la comprobación final antes de detener la grabación.

## Video 1 · Construcción de la base de datos

**Duración sugerida** 2–3 minutos.

1. Muestra [modelo-er.png](../database/modelo-er.png). Señala las cuatro entidades, sus claves primarias y las relaciones. Varios estudiantes pueden elegir el mismo proyecto. Un estudiante puede tener varias actividades y un docente puede supervisar varias.
2. Abre [01-schema.sql](../database/01-schema.sql). Señala `CREATE DATABASE`, las cuatro tablas, el `CHECK` de grado, la columna `DECIMAL` de horas y una llave foránea. Explica cómo la llave relaciona una actividad con su estudiante o docente.
3. Muestra la creación en una **base de demostración nueva**. En XAMPP puedes grabar la primera importación de `01-schema.sql` y luego la de `02-demo.sql`. Si ya estás trabajando con Docker, puedes crear una base de demostración en su propio volumen. Desde la carpeta del proyecto ejecuta:

   ```sh
   docker compose -p labor-video up --build -d --wait db
   docker compose -p labor-video exec db mariadb -ulabor_app -p labor_social
   ```

   Escribe `labor_app_local` cuando el cliente solicite la contraseña de demostración. En la consola SQL muestra y ejecuta:

   ```sql
   SHOW TABLES;
   SHOW CREATE TABLE actividades\G
   SELECT nombre, grado FROM estudiantes ORDER BY id;
   ```

   El servicio aislado solo levanta la base, así que el sitio habitual sigue disponible para los demás videos. Para detener esa base cuando termines, sal de MariaDB con `exit` y ejecuta `docker compose -p labor-video down`.
4. Cierra mostrando las cuatro tablas y los estudiantes cargados. En XAMPP puedes verlos en la pestaña **Estructura** de phpMyAdmin y abrir la tabla **estudiantes**.

**Guion**

> Soy [tu nombre], de [tu programa]. Este es el modelo de datos de la labor social escolar. Cada proyecto puede tener varios estudiantes y cada estudiante puede registrar varias actividades. En actividades guardo las llaves del estudiante y del docente responsable, además de fecha, horas y trabajo realizado. En el SQL creo la base y defino las llaves y restricciones. Aquí se ven las cuatro tablas y los datos ficticios que voy a usar para las pruebas.

## Video 2 · Ingreso de docente

**Duración sugerida** 1–2 minutos.

1. Abre **Docentes → Nuevo docente**. Usa documento `D-103`, nombre **Camila Pardo** y correo `camila.pardo@example.org`.
2. Pulsa **Guardar registro**. Enseña la confirmación y encuentra **Camila Pardo** en la tabla. Recarga para demostrar que quedó guardada.
3. En [app/modules.php](../app/modules.php) muestra la sección `'docentes'` y luego `writeRecord` con `INSERT INTO`. En [public/index.php](../public/index.php) muestra el `POST` que valida los valores y llama a esa función.
4. Si ese documento ya está registrado por un ensayo, para la toma final usa otro documento ficticio libre o elimina el registro de ensayo si no tiene actividades.

**Guion**

> En Docentes ingreso a Camila Pardo con documento y correo. Al guardar, PHP valida los campos y ejecuta una consulta preparada para insertar el registro. El documento tiene una restricción de unicidad. Aquí aparece la confirmación y la docente queda disponible para asignarla a nuevas actividades.

## Video 3 · Edición de estudiante

**Duración sugerida** 1–2 minutos.

1. Abre **Estudiantes**. Ubica a **Mateo Salazar**, documento `E-102`, y muestra que está en 10°.
2. Pulsa **Editar**, cambia el grado a **Undécimo (11°)** y guarda. Muestra el nuevo grado junto al mismo documento.
3. En [public/index.php](../public/index.php) muestra cómo se carga el registro por ID. En [app/modules.php](../app/modules.php) muestra cómo `writeRecord` utiliza `UPDATE ... WHERE id = ?` para guardar el cambio.
4. Si ya hiciste el cambio en un ensayo, puedes devolver a Mateo a 10° desde la aplicación antes de grabar.

**Guion**

> Voy a editar a Mateo Salazar. El formulario carga la información que ya existe. Cambio su grado a undécimo y guardo. El PHP recibe el identificador del estudiante y actualiza esa fila. En el listado se ve el mismo documento con el grado nuevo.

## Video 4 · Borrado de proyecto

**Duración sugerida** 1–2 minutos.

1. Abre **Proyectos** y ubica **Huerta de prueba**. Muestra que tiene **0** estudiantes.
2. Pulsa **Eliminar** y acepta la confirmación. Enseña el mensaje y que la fila desapareció.
3. En [public/index.php](../public/index.php) señala `DELETE FROM ... WHERE id = ?`. En [database/01-schema.sql](../database/01-schema.sql) muestra `fk_estudiante_proyecto` y `ON DELETE RESTRICT`.
4. Para repetir la toma, usa **Nuevo proyecto** para recrear **Huerta de prueba** con una descripción y déjalo sin estudiantes.

**Guion**

> Selecciono Huerta de prueba, un proyecto que tiene cero estudiantes asociados. Confirmo la operación y el sistema elimina esa fila. La llave foránea protege los proyectos que tienen estudiantes asignados. Ya se ve que Huerta de prueba salió de la lista.

## Video 5 · Consulta para generar la Imagen No. 1

**Duración sugerida** 2–3 minutos.

1. Abre **Informes**, busca `Valentina Rojas` o `E-101` y pulsa **Ver informe**.
2. Muestra el encabezado de la Institución XYZ con su escudo y bandera de muestra, el nombre, el grado 11° y el proyecto **Gestión Documental**.
3. Recorre las columnas de la plantilla. En las primeras filas señala la actividad de 8 horas y cómo el acumulado avanza de 8 a 16 y luego a 24. Baja hasta **80 / 80 h** y **Meta cumplida**.
4. En [app/reports.php](../app/reports.php) muestra `findStudents` para la búsqueda, el `JOIN` que recupera el docente y `SUM(a.horas) OVER (...)` para el acumulado en orden por fecha e ID.
5. Regresa al informe. Pulsa **Imprimir informe** y enseña la vista de impresión. Si te queda tiempo, abre el informe de Mateo para enseñar un ejemplo en progreso con **4 / 80 h**.

**Guion**

> Busco a Valentina por su nombre y abro el registro detallado. En el encabezado aparecen su grado y su proyecto. Cada fila muestra la fecha, las horas de esa actividad, el acumulado, el trabajo y quién lo supervisó. La consulta suma las horas hasta la fila actual en orden cronológico. Aquí las diez actividades de ocho horas dan un total de ochenta, por eso aparece Meta cumplida. Desde este botón puedo imprimir el informe o guardarlo como PDF.

## Después de grabar

1. Exporta los cinco videos en MP4. Súbelos al servicio de video o almacenamiento que acepte tu docente. Usa los permisos de acceso que correspondan a la entrega y revisa los cinco enlaces desde una ventana privada o una cuenta con los mismos permisos que tendrá el docente.
2. En [app/config.php](../app/config.php), pega cada enlace HTTPS en su título correspondiente. Recarga Inicio y abre los cinco enlaces desde **Presentación del proyecto**.
3. Abre [Parcial3_basededatos-rellenable.pdf](Parcial3_basededatos-rellenable.pdf). En la página 3 pega los mismos enlaces en los cinco campos debajo de sus títulos. Guarda una copia con los cambios. Si tu visor no guarda formularios, usa otro visor con soporte para campos PDF. Reabre la copia y comprueba que se ven los cinco enlaces.
4. Genera un ZIP actualizado con el SQL, la imagen del modelo, los archivos PHP/HTML/CSS/JS y el PDF completado. Puedes incluir la guía y el README. Excluye las credenciales, la carpeta `.git` y archivos de configuración privados.
5. Extrae el ZIP en otra carpeta y comprueba que trae el PNG, que el PDF conserva los enlaces y que el nombre, programa y enlaces quedaron en `app/config.php`.
