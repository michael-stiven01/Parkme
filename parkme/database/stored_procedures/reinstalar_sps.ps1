# reinstalar_sps.ps1
# Re-instala todos los Stored Procedures en la base de datos local de Windows
# Uso: .\database\stored_procedures\reinstalar_sps.ps1

$host_db   = "127.0.0.1"
$port_db   = "3306"
$user_db   = "root"
$pass_db   = ""          # Cambia si tu root tiene contraseña
$database  = "parkme"

$sps = @(
    "sp_crear_usuario.sql",
    "sp_verificar_codigo_email.sql",
    "sp_verificar_usuario.sql",
    "sp_guardar_codigo_recuperacion.sql",
    "sp_actualizar_contrasena.sql",
    "sp_registrar_bitacora.sql",
    "sp_listar_tipos_vehiculo.sql"
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

foreach ($sp in $sps) {
    $filePath = Join-Path $scriptDir $sp
    Write-Host "Ejecutando $sp ..." -ForegroundColor Cyan
    & mysql -h $host_db -P $port_db -u $user_db --password="$pass_db" $database `
            --delimiter="//" -e (Get-Content $filePath -Raw)
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  OK" -ForegroundColor Green
    } else {
        Write-Host "  ERROR en $sp" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Listo. Todos los SPs han sido reinstalados." -ForegroundColor Yellow
