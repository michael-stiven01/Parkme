# Procedimientos Almacenados (Stored Procedures)

De acuerdo a las reglas de arquitectura de "Parkme!", está **estrictamente prohibido** utilizar SQL crudo para leer o modificar la base de datos de manera normal (salvo migraciones).

## Rutas y Convenciones

1. Cada *Stored Procedure* se debe guardar en un archivo individual `.sql` correspondiente a su nombre dentro de este directorio (`database/stored_procedures/`).
2. Para ejecutar un Stored Procedure en la aplicación en Laravel, debes utilizar siempre el Helper `App\Helpers\SPHelper`.
