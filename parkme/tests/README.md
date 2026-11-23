# Pruebas unitarias de Parkme!

Suite de PHPUnit 12 que cubre la lógica de negocio de Parkme!: el acceso a datos (`SPHelper`), los servicios (`AuthService`, `ParqueoService`, `BitacoraService`, `SesionService`), el control de acceso (middleware), el manejador global de errores y el cálculo del cobro de parqueo (`sp_calcular_tarifa`).

## Organización

| Carpeta | Qué prueba | ¿Necesita MySQL? |
|---|---|---|
| `Unit/Helpers` | `SPHelper`: arma el `CALL`, maneja los fallos de BD y evita la recursión de la bitácora | No (BD simulada) |
| `Unit/Services` | Registro y verificación de usuarios, ingreso y salida de vehículos, tickets, bitácora y sesión | No (BD y correo simulados) |
| `Unit/Middleware` | Reglas de acceso: sesión, correo verificado, empresa registrada, pantallas solo para invitados | No |
| `Unit/Exceptions` | Registro y respuesta ante errores no controlados | No |
| `Unit/BaseDatos` | El cálculo de tarifas en `sp_calcular_tarifa` (grupo `bd`) | Sí: `parkme_test` |

Cada prueba tiene un identificador `UT-NN`, un docblock con el escenario y el valor esperado, y un `#[TestDox]` legible.

## Preparar la base de datos de pruebas (solo para el grupo `bd`)

Las pruebas de `Unit/BaseDatos` corren contra una base de datos **separada** de la de desarrollo. Cada prueba se ejecuta dentro de una transacción que se revierte al final, así que no deja datos. Si la BD no está disponible, esas pruebas se marcan como omitidas (*skipped*) y el resto de la suite corre igual.

```bash
# 1. Crear la BD y dar permisos al usuario de la app (como root)
mysql -uroot -p -e "CREATE DATABASE parkme_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON parkme_test.* TO 'parkme'@'localhost';"

# 2. Crear el esquema y la semilla
DB_DATABASE=parkme_test php artisan migrate --seed

# 3. Cargar los procedimientos almacenados
for f in database/stored_procedures/*.sql; do mysql -uparkme -p parkme_test < "$f"; done
```

La conexión usa el host, el usuario y la contraseña del `.env`. El nombre de la base se puede cambiar con la variable `DB_TEST_DATABASE`.

## Ejecutar

```bash
php vendor/bin/phpunit --testdox                  # toda la suite
php vendor/bin/phpunit --testdox --exclude-group bd   # sin MySQL
```

## Cobertura

Requiere Xdebug (`xdebug.mode=coverage`) o PCOV. El alcance se define en `phpunit.xml` (`<source>`): `app/Services`, `app/Helpers`, `app/Http/Middleware` y `app/Exceptions`.

```bash
php vendor/bin/phpunit --path-coverage --coverage-text --coverage-html ../docs/cobertura/html --coverage-clover ../docs/cobertura/clover.xml
```

El último reporte generado está en [`docs/cobertura/html/index.html`](../../docs/cobertura/html/index.html) y la salida de consola en [`docs/evidencias/`](../../docs/evidencias/).
