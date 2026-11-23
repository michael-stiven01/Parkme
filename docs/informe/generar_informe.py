#!/usr/bin/env python3
"""
Genera el informe «Tarea 2 - Pruebas Unitarias e Informe de Cobertura» (PSyHDA)
a partir de los artefactos REALES de la última corrida de PHPUnit:

  docs/evidencias/junit.xml                -> resultado PASS/FAIL y tiempo de cada prueba
  docs/evidencias/salida_phpunit.txt       -> log de consola y resumen de cobertura (líneas, ramas, rutas, métodos)
  docs/evidencias/cobertura_app_completa.txt -> cobertura de app/ completo (contexto)
  docs/cobertura/clover.xml                -> líneas no ejecutadas

El catálogo de pruebas (lógica aplicada y valor esperado) está en CATALOGO y
debe coincidir con los docblocks de parkme/tests/Unit/**.

Uso:
    python docs/informe/generar_informe.py [--salida RUTA.pdf]
Requiere: reportlab (pip install -r docs/informe/requirements.txt)
"""
from __future__ import annotations

import argparse
import html
import re
import subprocess
import xml.etree.ElementTree as ET
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import cm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (BaseDocTemplate, Frame, KeepTogether, NextPageTemplate, PageBreak,
                                PageTemplate, Paragraph, Spacer, Table, TableStyle, XPreformatted)

RAIZ = Path(__file__).resolve().parents[2]
EVIDENCIAS = RAIZ / "docs" / "evidencias"
COBERTURA = RAIZ / "docs" / "cobertura"
APP = RAIZ / "parkme"

REPO_URL = "https://github.com/michael-stiven01/Parkme"
RAMA = "main"

# ── Datos de portada ────────────────────────────────────────────────────────
ASIGNATURA = "Pruebas de Software y Herramientas de Desarrollo Avanzado (PSyHDA)"
ENTREGABLE = "Tarea 2 - Pruebas Unitarias e Informe de Cobertura"
PROFESOR = "Ing. Oscar Dario Bautista Loza, Ph.D."
INTEGRANTES = ["Sebastian Acuña", "Michael Vergara"]
PROYECTO = "Parkme! — Sistema de gestión inteligente de parqueaderos"
FECHA = "Noviembre de 2026"

# ── Catálogo de pruebas ─────────────────────────────────────────────────────
# tipo: E = éxito · B = borde/límite · N = nulos, vacíos o inválidos · X = errores y excepciones
TIPOS = {
    "E": "Éxito",
    "B": "Borde / límite",
    "N": "Nulo / vacío / inválido",
    "X": "Error / excepción",
}

GRUPOS = [
    ("SPHelper — acceso a datos por procedimientos almacenados", "app/Helpers/SPHelper.php", [
        ("UT-01", "E", "Construye el CALL con un placeholder por parámetro y devuelve las filas",
         "Se simula la BD y se ejecuta sp_buscar_vehiculo_por_placa con [7, 'ABC123']. Se verifica la sentencia exacta que recibe DB::select y que el resultado sea idéntico a las filas simuladas.",
         "Sentencia «CALL sp_buscar_vehiculo_por_placa(?, ?)» con [7, 'ABC123']; devuelve las mismas filas."),
        ("UT-02", "B", "Sin parámetros genera «CALL sp()» sin placeholders",
         "Se ejecuta sp_listar_tipos_vehiculo con el arreglo de parámetros vacío (límite inferior: 0 parámetros).",
         "«CALL sp_listar_tipos_vehiculo()» sin «?» ni comas sueltas; resultado []."),
        ("UT-03", "X", "Ante una excepción de BD devuelve null y registra el fallo en la bitácora",
         "La BD lanza «Duplicate entry» al ejecutar sp_crear_cliente. Se verifica que la excepción no se propague y que se llame a sp_registrar_bitacora con acción, módulo, detalle e IP.",
         "Retorna null; 1 registro en bitácora: «Ejecución Fallida de SP» / «Base de Datos» / «Error en sp_crear_cliente: Duplicate entry» / 127.0.0.1."),
        ("UT-04", "X", "Si falla sp_registrar_bitacora no reintenta registrar (evita el ciclo infinito)",
         "Falla el propio sp_registrar_bitacora. El mock admite una sola llamada a la BD. Validada por mutación: al quitar la guarda, la prueba cae por recursión infinita.",
         "Exactamente 1 llamada a la BD; retorna null."),
    ]),
    ("AuthService — registro, verificación y recuperación", "app/Services/AuthService.php", [
        ("UT-05", "E", "Registra al usuario con contraseña cifrada, código de 6 caracteres que vence en 15 min y lo envía por correo",
         "Reloj fijo en 2026-11-23 10:00:00, BD simulada y Mail::fake(). Se capturan los parámetros enviados a sp_crear_usuario y se verifica el hash con Hash::check, el formato del código y su vencimiento.",
         "Hash ≠ texto plano y válido; código /^[A-Z0-9]{6}$/; vence «2026-11-23 10:15:00»; 1 correo CodigoVerificacion con ese código."),
        ("UT-06", "E", "Verificar código devuelve true cuando el SP responde estado = 1",
         "sp_verificar_codigo_email simulado responde {estado: 1}.",
         "true"),
        ("UT-07", "X", "Verificar código lanza excepción con el mensaje del SP cuando estado = 0",
         "El SP responde {estado: 0, mensaje: 'El código ha expirado'}.",
         "Exception con el mensaje «El código ha expirado»."),
        ("UT-08", "N", "Verificar código lanza «Error al verificar el código.» si el SP no devuelve filas",
         "Código vacío ('') y el SP devuelve un conjunto vacío.",
         "Exception «Error al verificar el código.»"),
        ("UT-09", "E", "Envía el código de recuperación con el correo CodigoRecuperacion al destinatario correcto",
         "Se invoca enviarCodigoRecuperacion('ana@parkme.test', 'R3C0V3') con Mail::fake().",
         "1 correo CodigoRecuperacion a ana@parkme.test con R3C0V3; 0 correos de verificación."),
        ("UT-36", "X", "Si sp_crear_usuario falla, el registro lanza excepción y no envía el código de verificación",
         "Regresión de un defecto hallado al escribir la suite: el servicio ignoraba el null de SPHelper y enviaba el código de una cuenta inexistente. La BD lanza «Duplicate entry».",
         "Exception «No se pudo guardar el usuario. Intenta de nuevo en unos minutos.»; 0 correos enviados."),
    ]),
    ("ParqueoService — ingreso, salida y tickets", "app/Services/ParqueoService.php", [
        ("UT-10", "E", "Registrar ingreso envía los 4 parámetros en orden y devuelve la fila del SP como arreglo",
         "Ingreso con empresa 1, vehículo 20, cupo 5, usuario 3; el SP responde un objeto con id_registro 99 y cupo A-05.",
         "CALL con [1, 20, 5, 3]; arreglo con error = null, id_registro = 99, codigo_cupo = 'A-05'."),
        ("UT-11", "N", "Registrar ingreso devuelve error SP_SIN_RESPUESTA cuando la BD no responde",
         "La BD lanza «Lost connection»; SPHelper devuelve null al servicio.",
         "['error' => 'SP_SIN_RESPUESTA']"),
        ("UT-12", "N", "Registrar salida propaga el error REGISTRO_ACTIVO_NO_ENCONTRADO del SP",
         "Placa XYZ999 sin registro activo: el SP la rechaza. Se verifica el orden de parámetros (empresa, placa, usuario).",
         "error = 'REGISTRO_ACTIVO_NO_ENCONTRADO'; id_registro = null."),
        ("UT-13", "N", "Ticket de ingreso sin correo del cliente: no envía, no marca en BD y deja advertencia",
         "correo_cliente = '' con Mail::fake() y Log::spy(); el mock de BD no admite llamadas.",
         "0 correos; 0 llamadas a la BD; 1 advertencia en el log."),
        ("UT-14", "E", "Ticket de salida con correo: se envía al cliente y se marca como enviado en la BD",
         "Registro 99 con correo y valor cobrado 6000.",
         "1 correo TicketSalida a cliente@parkme.test con valor 6000; CALL sp_marcar_ticket_salida(99)."),
        ("UT-15", "B", "Ticket de ingreso sin id_registro: envía el correo pero no intenta marcarlo en la BD",
         "Datos con correo pero sin id_registro.",
         "1 correo TicketIngreso; 0 llamadas a la BD."),
        ("UT-16", "X", "Si el servidor de correo falla, captura la excepción, la registra y no marca el ticket",
         "Mail::to() lanza «SMTP no disponible» al enviar el ticket de ingreso.",
         "Sin excepción hacia arriba; 1 Log::error con el mensaje; 0 llamadas a la BD."),
        ("UT-37", "N", "Registrar salida devuelve error SP_SIN_RESPUESTA cuando la BD no responde",
         "La BD lanza «Lost connection» al registrar la salida de ABC123.",
         "['error' => 'SP_SIN_RESPUESTA']"),
        ("UT-38", "X", "Ticket de salida: sin correo no envía, y si el SMTP falla no propaga el error ni marca el ticket",
         "Primero con correo_cliente = null; luego con correo y el SMTP caído.",
         "1 advertencia; 1 Log::error «…ticket de salida…»; 0 llamadas a la BD; sin excepción."),
        ("UT-39", "E", "Ticket de ingreso con correo: se envía al cliente y se marca como enviado en la BD",
         "Registro 99 con correo, placa ABC123 y cupo A-05. Prueba agregada al revisar el reporte de ramas, que mostraba sin ejecutar el marcado del ticket de ingreso.",
         "1 correo TicketIngreso a cliente@parkme.test con placa y cupo; CALL sp_marcar_ticket_ingreso(99)."),
    ]),
    ("BitacoraService y SesionService — auditoría y sesión", "app/Services/", [
        ("UT-17", "N", "Bitácora con usuario y detalle nulos: escribe «N/A» en el log y envía null al SP con la IP",
         "registrar('Salida de vehículo registrada', 'Parqueo') sin usuario ni detalle.",
         "Log «… Usuario ID: N/A | Detalle: N/A»; SP con [null, null, acción, módulo, null, '127.0.0.1']."),
        ("UT-18", "E", "Iniciar sesión autentica al usuario y usuarioActual() no expone la contraseña",
         "Antes de iniciar se verifica que no haya sesión; luego se inicia con los datos de sp_verificar_usuario (incluido el hash).",
         "Antes: no autenticado y null. Después: autenticado, id 3, ROL administrador, SIN la clave CONTRASENA."),
        ("UT-19", "E", "Cerrar sesión deja al usuario como invitado y regenera el token CSRF",
         "Se inicia sesión, se guarda el token CSRF y se cierra la sesión.",
         "Auth::check() = false; token CSRF distinto del anterior."),
    ]),
    ("Middleware — control de acceso", "app/Http/Middleware/", [
        ("UT-20", "N", "Sin sesión, una ruta protegida redirige al login",
         "SesionService simulado sin usuario; petición a la ruta dashboard.",
         "Redirección a /login."),
        ("UT-21", "B", "Correo sin verificar: redirige a verificación, salvo si ya está en esa pantalla",
         "Usuario con CORREO_VERIFICADO = 0. Se prueba hacia dashboard y hacia la propia pantalla de verificación (límite que evita el bucle).",
         "dashboard → /verificar-correo; verification.notice → 200."),
        ("UT-22", "N", "Usuario verificado sin empresa (id_empresa null) es obligado a registrar la empresa",
         "Usuario verificado con id_empresa = null, hacia clientes y hacia empresa.registrar.",
         "clientes → /empresa/registrar; empresa.registrar → 200."),
        ("UT-23", "E", "Usuario autenticado, verificado y con empresa accede a la ruta protegida",
         "Usuario completo hacia dashboard.",
         "200 con «contenido protegido»."),
        ("UT-24", "E", "Las pantallas de invitado envían al dashboard a quien ya tiene sesión",
         "SoloInvitadosMiddleware con y sin sesión hacia login.",
         "Con sesión → /dashboard; sin sesión → 200."),
    ]),
    ("Handler — manejo global de excepciones", "app/Exceptions/Handler.php", [
        ("UT-25", "X", "Registra en el log las excepciones no controladas e ignora los errores HTTP (404)",
         "report() con NotFoundHttpException y luego con RuntimeException('División por cero en el cobro'), con Log::spy().",
         "404: sin Log::error. Runtime: 1 Log::error «Excepción no controlada: …» con url, method, timestamp y user_id = 'invitado'."),
        ("UT-26", "X", "Responde 500 genérico sin exponer el detalle interno, y delega los errores HTTP a Laravel",
         "render() con una excepción cuyo mensaje contiene «SQLSTATE… contraseña=secreta», y luego con un 404.",
         "Respuesta 500 cuyo cuerpo NO contiene «SQLSTATE»; para el 404 retorna null."),
    ]),
    ("sp_calcular_tarifa — cálculo del cobro (MySQL, BD parkme_test)", "database/stored_procedures/sp_calcular_tarifa.sql", [
        ("UT-27", "B", "Tarifa por hora: una estadía de 0 minutos cobra la hora mínima ($3.000)",
         "Tarifa por_hora de $3.000; ingreso y salida 08:00 (valor mínimo de duración).",
         "minutos = 0; total = $3.000 (mínimo 1 hora)."),
        ("UT-28", "B", "Tarifa por hora: 60 min cobra 1 hora y 61 min cobra 2 horas (redondeo hacia arriba)",
         "Misma tarifa; salidas 09:00 y 09:01 (frontera exacta de la hora).",
         "60 min → $3.000; 61 min → $6.000."),
        ("UT-29", "E", "Tarifa por fracción de 15 min: 31 minutos cobra 3 fracciones ($2.400)",
         "Tarifa por_fraccion de $800 cada 15 min; 08:00 → 08:31.",
         "minutos = 31; total = $2.400."),
        ("UT-30", "E", "Tarifa plana por día: 25 horas cobra 2 días ($40.000)",
         "Tarifa plana_dia de $20.000; 2026-11-20 08:00 → 2026-11-21 09:00.",
         "minutos = 1500; total = $40.000."),
        ("UT-31", "B", "Tarifa especial nocturna 22:00–06:00: salida a las 02:30 aplica la especial ($10.000)",
         "Conviven por_hora $3.000 y especial $10.000 (22:00–06:00, cruza la medianoche). Ingreso 23:00, salida 02:30.",
         "Tarifa aplicada «Tarifa especial»; total = $10.000."),
        ("UT-32", "B", "Fuera de la franja especial (salida 14:00) aplica la tarifa por hora ($9.000)",
         "Mismas tarifas; 11:30 → 14:00 (2 h 30 min).",
         "Tarifa aplicada «Tarifa por_hora»; total = $9.000 (3 horas)."),
        ("UT-33", "E", "La tarifa del tipo de vehículo prevalece sobre la genérica (moto $2.000 vs. auto $6.000)",
         "Genérica por_hora $3.000 y otra solo para motocicleta de $1.000; 2 horas para cada tipo.",
         "Motocicleta → $2.000; automóvil → $6.000."),
        ("UT-34", "N", "Con la única tarifa inactiva: id_tarifa null, «Sin Tarifa» y cobro $0",
         "Se crea una tarifa y se desactiva con sp_cambiar_estado_tarifa; 4 horas.",
         "id_tarifa = null; nombre «Sin Tarifa»; total = $0."),
        ("UT-35", "N", "Salida anterior al ingreso (-30 min): no cobra negativo, cobra el mínimo ($3.000)",
         "Entrada inválida: salida 08:00 anterior al ingreso 08:30.",
         "minutos = -30; total = $3.000 (> 0)."),
    ]),
]

# Justificación de las líneas no ejecutadas (archivo, línea) -> texto
JUSTIFICACIONES = {
    ("Handler.php", 53): (
        "Rama de modo mantenimiento (<i>php artisan down</i>): en ese estado la respuesta se delega a Laravel. "
        "Simularla exige sustituir el contenedor completo de la aplicación (el facade App es el propio contenedor), "
        "lo que contaminaría al resto de la suite. Es la misma acción de «delegar» (return null) que sí se verifica "
        "en UT-26 para los errores HTTP."),
    ("SPHelper.php", 63): (
        "Código defensivo: se ejecuta solo si falla el registro en la bitácora <i>mientras</i> se maneja otro fallo de BD. "
        "BitacoraService usa a su vez SPHelper, que ya captura los errores de BD, de modo que esta rama solo se "
        "alcanzaría si fallara el propio logger o request(). El caso relevante, que la bitácora falle sin recursión, "
        "lo cubre UT-04."),
}

# ── Lectura de artefactos ───────────────────────────────────────────────────

def leer_resultados_junit() -> dict[str, dict]:
    resultados = {}
    for caso in ET.parse(EVIDENCIAS / "junit.xml").iter("testcase"):
        nombre = caso.get("name", "")
        m = re.match(r"test_ut(\d+)_", nombre)
        if not m:
            continue
        estado = "PASS"
        if caso.find("failure") is not None or caso.find("error") is not None:
            estado = "FAIL"
        elif caso.find("skipped") is not None:
            estado = "SKIP"
        resultados[f"UT-{int(m.group(1)):02d}"] = {
            "estado": estado, "tiempo": float(caso.get("time", 0)), "metodo": nombre, "clase": caso.get("class", ""),
        }
    return resultados


def leer_resumen_junit() -> dict:
    raiz = ET.parse(EVIDENCIAS / "junit.xml").getroot()
    suite = raiz if raiz.tag == "testsuite" else raiz.find("testsuite")
    return {k: suite.get(k) for k in ("tests", "assertions", "failures", "errors", "skipped", "time")}


_RE_METRICA = re.compile(r"(Classes|Methods|Paths|Branches|Lines):\s+([\d.]+)%\s+\(\s*(\d+)/\s*(\d+)\)")


def leer_cobertura_texto(ruta: Path) -> tuple[dict, list]:
    texto = ruta.read_text(encoding="utf-8")
    bloque = texto[texto.index("Summary:"):]
    lineas = bloque.splitlines()
    resumen, por_clase, clase = {}, [], None
    for linea in lineas[1:]:
        if linea.startswith("App\\"):
            clase = linea.strip()
            continue
        metricas = {m[0]: (float(m[1]), int(m[2]), int(m[3])) for m in _RE_METRICA.findall(linea)}
        if not metricas:
            continue
        if clase is None:
            resumen.update(metricas)
        else:
            por_clase.append((clase, metricas))
            clase = None
    return resumen, por_clase


def leer_lineas_no_cubiertas() -> list[tuple[str, int]]:
    faltantes = []
    for archivo in ET.parse(COBERTURA / "clover.xml").iter("file"):
        for linea in archivo.iter("line"):
            if linea.get("type") == "stmt" and linea.get("count") == "0":
                faltantes.append((archivo.get("name"), int(linea.get("num"))))
    return faltantes


def commit_actual() -> str:
    try:
        return subprocess.check_output(["git", "-C", str(RAIZ), "rev-parse", "--short", "HEAD"], text=True).strip()
    except Exception:
        return "N/D"


# ── Estilos ─────────────────────────────────────────────────────────────────
FUENTES = Path("C:/Windows/Fonts")
pdfmetrics.registerFont(TTFont("Texto", str(FUENTES / "segoeui.ttf")))
pdfmetrics.registerFont(TTFont("Texto-Negrita", str(FUENTES / "segoeuib.ttf")))
pdfmetrics.registerFont(TTFont("Texto-Cursiva", str(FUENTES / "segoeuii.ttf")))
pdfmetrics.registerFont(TTFont("Texto-NegritaCursiva", str(FUENTES / "segoeuiz.ttf")))
pdfmetrics.registerFont(TTFont("Mono", str(FUENTES / "consola.ttf")))
pdfmetrics.registerFont(TTFont("Simbolos", str(FUENTES / "seguisym.ttf")))
pdfmetrics.registerFontFamily("Texto", normal="Texto", bold="Texto-Negrita", italic="Texto-Cursiva", boldItalic="Texto-NegritaCursiva")

AZUL = colors.HexColor("#1F3A5F")
AZUL_CLARO = colors.HexColor("#E8EEF5")
GRIS = colors.HexColor("#5B6573")
GRIS_LINEA = colors.HexColor("#C9D1DB")
VERDE = colors.HexColor("#1E7B45")
ROJO = colors.HexColor("#B3261E")

E = {
    "cuerpo": ParagraphStyle("cuerpo", fontName="Texto", fontSize=10, leading=14.5, spaceAfter=6),
    "h1": ParagraphStyle("h1", fontName="Texto-Negrita", fontSize=16, leading=20, textColor=AZUL, spaceBefore=4, spaceAfter=10),
    "h2": ParagraphStyle("h2", fontName="Texto-Negrita", fontSize=12, leading=16, textColor=AZUL, spaceBefore=10, spaceAfter=5),
    "celda": ParagraphStyle("celda", fontName="Texto", fontSize=8, leading=10.5),
    "celda_n": ParagraphStyle("celda_n", fontName="Texto-Negrita", fontSize=8, leading=10.5),
    "celda_enc": ParagraphStyle("celda_enc", fontName="Texto-Negrita", fontSize=8, leading=10.5, textColor=colors.white),
    "nota": ParagraphStyle("nota", fontName="Texto", fontSize=8.5, leading=12, textColor=GRIS),
    "codigo": ParagraphStyle("codigo", fontName="Mono", fontSize=7.2, leading=9.2),
    "vineta": ParagraphStyle("vineta", fontName="Texto", fontSize=10, leading=14.5, leftIndent=14, bulletIndent=2, spaceAfter=3),
}


def p(texto: str, estilo: str = "cuerpo") -> Paragraph:
    return Paragraph(texto, E[estilo])


def vinetas(items: list[str]) -> list[Paragraph]:
    return [Paragraph(t, E["vineta"], bulletText="•") for t in items]


def tabla(datos, anchos, encabezado=True, zebra=True, extra=None) -> Table:
    t = Table(datos, colWidths=anchos, repeatRows=1 if encabezado else 0)
    estilo = [
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 4), ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, -1), 3), ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
        ("LINEBELOW", (0, 0), (-1, -1), 0.4, GRIS_LINEA),
    ]
    if encabezado:
        estilo += [("BACKGROUND", (0, 0), (-1, 0), AZUL)]
    if zebra:
        for i in range(1 if encabezado else 0, len(datos)):
            if i % 2 == 0:
                estilo.append(("BACKGROUND", (0, i), (-1, i), colors.HexColor("#F5F7FA")))
    t.setStyle(TableStyle(estilo + (extra or [])))
    return t


# ── Plantillas de página ────────────────────────────────────────────────────

def pie(canvas, doc):
    canvas.saveState()
    canvas.setFont("Texto", 8)
    canvas.setFillColor(GRIS)
    canvas.drawString(2 * cm, 1.2 * cm, "PSyHDA · Tarea 2 - Pruebas Unitarias e Informe de Cobertura · Parkme!")
    canvas.drawRightString(A4[0] - 2 * cm, 1.2 * cm, f"Página {doc.page}")
    canvas.setStrokeColor(GRIS_LINEA)
    canvas.line(2 * cm, 1.55 * cm, A4[0] - 2 * cm, 1.55 * cm)
    canvas.restoreState()


def construir(salida: Path) -> None:
    resultados = leer_resultados_junit()
    resumen_junit = leer_resumen_junit()
    resumen, por_clase = leer_cobertura_texto(EVIDENCIAS / "salida_phpunit.txt")
    resumen_global, _ = leer_cobertura_texto(EVIDENCIAS / "cobertura_app_completa.txt")
    no_cubiertas = leer_lineas_no_cubiertas()
    commit = commit_actual()
    log_consola = (EVIDENCIAS / "salida_phpunit.txt").read_text(encoding="utf-8")

    todas = [c for _, _, casos in GRUPOS for c in casos]
    faltan = [c[0] for c in todas if c[0] not in resultados]
    sobran = sorted(set(resultados) - {c[0] for c in todas})
    if faltan or sobran:
        raise SystemExit(f"El catálogo no coincide con junit.xml. Sin resultado: {faltan}. Sin catálogo: {sobran}")

    n_pruebas = len(todas)
    n_pass = sum(1 for c in todas if resultados[c[0]]["estado"] == "PASS")
    conteo_tipos = {k: sum(1 for c in todas if c[1] == k) for k in TIPOS}

    doc = BaseDocTemplate(str(salida), pagesize=A4, leftMargin=2 * cm, rightMargin=2 * cm, topMargin=2 * cm, bottomMargin=2.2 * cm,
                          title="Tarea 2 - Pruebas Unitarias e Informe de Cobertura — Parkme!",
                          author=", ".join(INTEGRANTES), subject=ASIGNATURA)
    marco = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="marco")
    doc.addPageTemplates([PageTemplate(id="portada", frames=[marco]), PageTemplate(id="normal", frames=[marco], onPage=pie)])
    h = []

    # ── Portada ──
    portada = lambda txt, tam, fuente="Texto", color=colors.black, antes=0, despues=0: Paragraph(
        txt, ParagraphStyle("p", fontName=fuente, fontSize=tam, leading=tam * 1.3, alignment=TA_CENTER, textColor=color, spaceBefore=antes, spaceAfter=despues))
    h += [Spacer(1, 2.2 * cm),
          portada(ASIGNATURA, 13, "Texto-Negrita", AZUL, despues=1.6 * cm),
          portada("Tarea 2", 14, "Texto", GRIS),
          portada("Pruebas Unitarias e Informe de Cobertura", 24, "Texto-Negrita", AZUL, antes=4, despues=0.5 * cm),
          portada(f"Proyecto Integrador: {PROYECTO}", 12, "Texto", colors.black, despues=2 * cm)]
    filas = [
        ["Profesor", PROFESOR],
        ["Integrantes", "<br/>".join(INTEGRANTES)],
        ["Repositorio", f'<link href="{REPO_URL}" color="#1F3A5F"><u>{REPO_URL}</u></link>'],
        ["Pruebas", f'<link href="{REPO_URL}/tree/{RAMA}/parkme/tests/Unit" color="#1F3A5F"><u>parkme/tests/Unit</u></link>'],
        ["Cobertura", f'<link href="{REPO_URL}/tree/{RAMA}/docs/cobertura" color="#1F3A5F"><u>docs/cobertura</u></link>'],
        ["Versión evaluada", f"rama {RAMA}, commit {commit}"],
        ["Fecha", FECHA],
    ]
    t = Table([[p(a, "celda_n"), p(b, "cuerpo")] for a, b in filas], colWidths=[4 * cm, 11 * cm], hAlign="CENTER")
    t.setStyle(TableStyle([("VALIGN", (0, 0), (-1, -1), "MIDDLE"), ("LINEBELOW", (0, 0), (-1, -1), 0.4, GRIS_LINEA),
                           ("TOPPADDING", (0, 0), (-1, -1), 6), ("BOTTOMPADDING", (0, 0), (-1, -1), 6)]))
    h += [t, Spacer(1, 1.6 * cm)]
    cifras = Table([[
        p(f'<font size="20" color="#1F3A5F"><b>{n_pass}/{n_pruebas}</b></font><br/><font color="#5B6573">pruebas unitarias aprobadas</font>', "celda"),
        p(f'<font size="20" color="#1F3A5F"><b>{resumen["Lines"][0]:.2f}%</b></font><br/><font color="#5B6573">cobertura de líneas (objetivo ≥ 80%)</font>', "celda"),
        p(f'<font size="20" color="#1F3A5F"><b>{resumen["Branches"][0]:.2f}%</b></font><br/><font color="#5B6573">cobertura de ramas</font>', "celda"),
    ]], colWidths=[5 * cm] * 3, hAlign="CENTER")
    cifras.setStyle(TableStyle([("BACKGROUND", (0, 0), (-1, -1), AZUL_CLARO), ("ALIGN", (0, 0), (-1, -1), "CENTER"),
                                ("TOPPADDING", (0, 0), (-1, -1), 10), ("BOTTOMPADDING", (0, 0), (-1, -1), 10),
                                ("LINEAFTER", (0, 0), (1, 0), 1, colors.white)]))
    for c in cifras._cellvalues[0]:
        c.style = ParagraphStyle("cifra", parent=E["celda"], alignment=TA_CENTER, leading=16)
    h += [cifras, NextPageTemplate("normal"), PageBreak()]

    # ── Sección 1 ──
    h.append(p("1. Descripción de la lógica de negocio probada", "h1"))
    h.append(p("<b>Parkme!</b> es una aplicación web multiempresa para administrar parqueaderos: registra empresas y su personal "
               "(administrador, recepcionista, portero), clientes y vehículos, cupos y tarifas, y controla el ingreso y la "
               "salida de vehículos, con el cálculo del cobro y el envío de tickets por correo. Está construida con "
               "<b>Laravel 13</b> y <b>Livewire 4</b> sobre <b>PHP 8.4</b>, con <b>MySQL 8.4</b> como base de datos."))
    h.append(p("Una regla de arquitectura del proyecto define dónde vive la lógica: <b>toda lectura o escritura de datos se hace por "
               "procedimientos almacenados</b>, invocados únicamente a través de <i>SPHelper</i>. Por eso la lógica de negocio "
               "está repartida en dos capas, y la suite prueba ambas:"))
    h += vinetas([
        "<b>Capa PHP (servicios, acceso a datos, control de acceso y errores).</b> Orquesta los procedimientos, interpreta sus "
        "respuestas y decide qué hacer ante los fallos. Se prueba de forma aislada: la base de datos y el correo se sustituyen "
        "por dobles de prueba (mocks del facade <i>DB</i>, <i>Mail::fake()</i>, <i>Log::spy()</i>), así que no se toca MySQL ni se envían correos.",
        "<b>Capa SQL (cálculo del cobro).</b> La regla central del negocio, cuánto paga un vehículo al salir, está en el "
        "procedimiento <i>sp_calcular_tarifa</i>. La unidad bajo prueba es ese procedimiento, ejecutado contra una base de datos de "
        "pruebas separada (<i>parkme_test</i>). Cada prueba crea sus datos dentro de una transacción que se revierte al terminar.",
    ])
    h.append(p("Reglas de negocio verificadas", "h2"))
    reglas = [
        ["Componente", "Reglas de negocio"],
        ["SPHelper", "Construye «CALL sp(?, …)» con un placeholder por parámetro; ante un fallo de BD no rompe la aplicación (devuelve null) "
                     "y deja constancia en la bitácora, sin entrar en recursión si el que falla es la propia bitácora."],
        ["AuthService", "La contraseña se guarda cifrada; el código de verificación tiene 6 caracteres alfanuméricos en mayúscula y vence a los "
                        "15 minutos; el código se valida contra la BD; no se envía código si la cuenta no se pudo crear."],
        ["ParqueoService", "El ingreso y la salida se delegan a los SP y sus errores de negocio (cupo no disponible, vehículo sin registro "
                           "activo…) se propagan como códigos; si la BD no responde se devuelve SP_SIN_RESPUESTA; el fallo del correo "
                           "nunca revierte un ingreso o una salida ya registrados, y un ticket solo se marca como enviado si se envió."],
        ["Bitácora y sesión", "Toda acción se audita aunque no haya usuario; la sesión nunca expone el hash de la contraseña; "
                              "al cerrar sesión se regenera el token CSRF."],
        ["Middleware", "Sin sesión → login; correo sin verificar → verificación; sin empresa → registro de empresa; las pantallas de invitado "
                       "no se muestran a quien ya tiene sesión; ninguna regla produce bucles de redirección."],
        ["Handler", "Los errores no controlados se registran y el usuario ve un 500 genérico que no expone detalles internos."],
        ["sp_calcular_tarifa", "por_hora: horas iniciadas × valor (mínimo 1); por_fraccion: fracciones iniciadas × valor (mínimo 1); "
                               "plana_dia: días iniciados × valor (mínimo 1); especial: valor fijo si la salida cae en la franja, que puede "
                               "cruzar la medianoche. Prioridad: tarifa del tipo de vehículo antes que la genérica, y especial antes que por "
                               "hora. Sin tarifa activa: $0 y «Sin Tarifa»."],
    ]
    h.append(tabla([[p(a, "celda_enc" if i == 0 else "celda_n"), p(b, "celda_enc" if i == 0 else "celda")] for i, (a, b) in enumerate(reglas)],
                   [3.4 * cm, 13.6 * cm]))
    h.append(Spacer(1, 6))
    h.append(p("Tipos de escenario cubiertos", "h2"))
    h.append(p(f"La suite tiene <b>{n_pruebas} pruebas unitarias</b> repartidas entre los cuatro tipos de escenario pedidos:"))
    h.append(tabla([[p("Tipo de escenario", "celda_enc"), p("Pruebas", "celda_enc"), p("Identificadores", "celda_enc")]] +
                   [[p(TIPOS[k], "celda_n"), p(str(conteo_tipos[k]), "celda"),
                     p(", ".join(c[0] for c in sorted(todas, key=lambda c: c[0]) if c[1] == k), "celda")] for k in TIPOS],
                   [4.5 * cm, 1.8 * cm, 10.7 * cm]))
    h.append(p("Herramientas: PHPUnit 12.5 (framework de pruebas estándar de PHP), Mockery (dobles de prueba), los fakes de Laravel "
               "(Mail, Log) y Xdebug 3.5 como driver de cobertura.", "nota"))

    # ── Sección 2 ──
    h.append(PageBreak())
    h.append(p("2. Registro detallado de las pruebas unitarias", "h1"))
    h.append(p("Cada prueba tiene un identificador <b>UT-NN</b> que también aparece en el código (nombre del método, docblock y "
               "<i>#[TestDox]</i>). La columna <b>Resultado</b> se toma del archivo JUnit de la corrida final "
               "(<i>docs/evidencias/junit.xml</i>), no se escribe a mano. Los tiempos incluyen la instrumentación de cobertura de "
               "rutas de Xdebug, que multiplica el costo de cada prueba. Las pruebas UT-36 a UT-39 se agregaron después de la primera "
               "medición de cobertura y aparecen en el grupo de su componente."))
    anchos = [1.25 * cm, 3.85 * cm, 5.65 * cm, 4.3 * cm, 1.95 * cm]
    for titulo, archivo, casos in GRUPOS:
        filas = [[p(x, "celda_enc") for x in ("ID", "Nombre de la prueba / tipo", "Lógica aplicada (escenario y aserciones)", "Valor esperado", "Resultado")]]
        for uid, tipo, nombre, logica, esperado in casos:
            r = resultados[uid]
            color = VERDE if r["estado"] == "PASS" else ROJO
            filas.append([
                p(f"<b>{uid}</b>", "celda"),
                p(f"{html.escape(nombre)}<br/><font color='#5B6573'><i>{TIPOS[tipo]}</i></font>", "celda"),
                p(html.escape(logica), "celda"),
                p(html.escape(esperado), "celda"),
                p(f"<font color='{color.hexval().replace('0x', '#')}'><b>{r['estado']}</b></font><br/><font color='#5B6573' size='7'>{r['tiempo'] * 1000:.0f} ms</font>", "celda"),
            ])
        # El título, la nota y la tabla van juntos: si la tabla no cabe, el bloque pasa entero a la página siguiente.
        h.append(KeepTogether([p(titulo, "h2"), p(f"Código bajo prueba: {archivo}", "nota"), Spacer(1, 3), tabla(filas, anchos)]))

    # ── Sección 3 ──
    h.append(PageBreak())
    h.append(p("3. Informe y explicación del reporte de cobertura", "h1"))
    h.append(p("La cobertura se midió con <b>PHPUnit 12.5.17 + Xdebug 3.5.3</b> en modo <i>path coverage</i>, que además de líneas "
               "mide ramas (cada salida de un if, ternario o catch) y rutas (cada combinación de ramas de un método). "
               "El alcance se declara en <i>phpunit.xml</i> (<i>&lt;source&gt;</i>) y corresponde a la capa de lógica de negocio en PHP: "
               "<i>app/Services</i>, <i>app/Helpers</i>, <i>app/Http/Middleware</i> y <i>app/Exceptions</i>."))
    h.append(p("Resumen de métricas", "h2"))
    objetivo = 80.0
    nombres = [("Lines", "Líneas"), ("Branches", "Ramas"), ("Paths", "Rutas"), ("Methods", "Métodos"), ("Classes", "Clases")]
    filas = [[p(x, "celda_enc") for x in ("Métrica", "Cubierto / total", "Porcentaje", "Frente al objetivo (≥ 80%)")]]
    for clave, etiqueta in nombres:
        pct, cub, tot = resumen[clave]
        if clave in ("Lines", "Branches"):
            juicio = f"<font color='#1E7B45'><b>Cumple</b></font> (+{pct - objetivo:.2f} pp)" if pct >= objetivo else f"<font color='#B3261E'><b>No cumple</b></font>"
        else:
            juicio = "<font color='#5B6573'>Métrica complementaria (ver explicación)</font>"
        filas.append([p(f"<b>{etiqueta}</b>", "celda"), p(f"{cub} / {tot}", "celda"), p(f"<b>{pct:.2f}%</b>", "celda"), p(juicio, "celda")])
    h.append(tabla(filas, [3 * cm, 3.4 * cm, 2.8 * cm, 7.8 * cm]))
    h.append(Spacer(1, 4))
    h.append(p(f"El objetivo se mide sobre la <b>cobertura de líneas</b>, la métrica estándar: <b>{resumen['Lines'][0]:.2f}%</b>, "
               f"{resumen['Lines'][0] - objetivo:.2f} puntos por encima del 80%. La cobertura de ramas también lo supera "
               f"({resumen['Branches'][0]:.2f}%)."))

    h.append(p("Cobertura por clase", "h2"))
    filas = [[p(x, "celda_enc") for x in ("Clase", "Líneas", "Ramas", "Rutas", "Métodos")]]
    for clase, m in por_clase:
        fmt = lambda k: f"{m[k][0]:.2f}% ({m[k][1]}/{m[k][2]})"
        corto = clase.replace("App\\Http\\", "").replace("App\\", "")
        filas.append([p(corto, "celda_n"), p(fmt("Lines"), "celda"), p(fmt("Branches"), "celda"), p(fmt("Paths"), "celda"), p(fmt("Methods"), "celda")])
    h.append(tabla(filas, [5.8 * cm, 2.85 * cm, 2.85 * cm, 2.85 * cm, 2.65 * cm]))

    h.append(p("Qué está cubierto", "h2"))
    h += vinetas([
        "<b>100% de líneas y ramas</b> en AuthService, BitacoraService, SesionService y en los dos middleware: cada regla de "
        "registro, verificación, sesión y control de acceso se ejecuta por al menos un camino de éxito y uno de fallo.",
        "<b>ParqueoService al 100% de líneas y ramas</b>: ingreso y salida (con respuesta, con error de negocio y con la BD caída) "
        "y los tickets de ingreso y de salida (enviados y marcados, sin correo, sin id de registro y con el SMTP caído).",
        "<b>SPHelper y Handler</b>: los caminos normales y de error están cubiertos; lo que falta son las 3 líneas defensivas que se "
        "justifican abajo.",
    ])
    h.append(p("Líneas no ejecutadas y justificación", "h2"))
    agrupadas: dict[tuple[str, int], list[int]] = {}
    for ruta, num in no_cubiertas:
        nombre = Path(ruta).name
        clave = next((k for k in JUSTIFICACIONES if k[0] == nombre and num in (k[1], k[1] + 1)), (nombre, num))
        agrupadas.setdefault(clave, []).append(num)
    filas = [[p(x, "celda_enc") for x in ("Archivo : línea", "Código", "Por qué no se ejecuta")]]
    for (nombre, _), nums in agrupadas.items():
        ruta = next(r for r, n in no_cubiertas if Path(r).name == nombre)
        fuente = Path(ruta).read_text(encoding="utf-8").splitlines()
        codigo = "<br/>".join(html.escape(fuente[n - 1].strip()) for n in nums)
        just = JUSTIFICACIONES.get((nombre, nums[0]), "Sin justificación registrada.")
        filas.append([p(f"<b>{nombre}</b> : {', '.join(map(str, nums))}", "celda"), p(f"<font name='Mono' size='7'>{codigo}</font>", "celda"), p(just, "celda")])
    h.append(tabla(filas, [3.3 * cm, 5.4 * cm, 8.3 * cm]))
    h.append(Spacer(1, 4))
    h.append(p("Sobre las métricas de métodos, clases y rutas", "h2"))
    h += vinetas([
        "<b>Métodos y clases.</b> En modo de cobertura de ramas, un método solo cuenta como cubierto si se ejecutan <i>todas</i> "
        "sus ramas, y una clase solo si todos sus métodos lo están. Una sola rama sin ejecutar basta para que el método no cuente. "
        "En SPHelper (un único método, 93,75% de líneas ejecutadas) faltan el catch interno y la rama «hay usuario autenticado» "
        "al atribuir el fallo en la bitácora, porque estas pruebas corren sin sesión. En Handler faltan la rama de mantenimiento y la "
        "misma rama de usuario autenticado en report(). Por eso estos porcentajes quedan por debajo del de líneas aunque el código "
        "esté casi todo ejecutado.",
        "<b>Rutas</b> cuenta cada combinación posible de ramas dentro de un método. Varias combinaciones son imposibles por diseño. "
        "Por ejemplo, en AutenticacionMiddleware no existe un usuario «sin sesión» que además tenga el correo verificado, porque la "
        "primera condición corta el flujo. Por eso esta métrica es complementaria y no se usa como objetivo.",
    ])
    h.append(p("Cobertura del cálculo de tarifas (SQL)", "h2"))
    h.append(p("Las herramientas de cobertura de PHP no instrumentan código SQL, así que <i>sp_calcular_tarifa</i> no aparece en "
               "las métricas anteriores. Su cobertura se verificó a mano, rama por rama:"))
    ramas_sp = [
        ["Rama del procedimiento", "Pruebas", "Estado"],
        ["Selección: tarifa del tipo de vehículo antes que la genérica", "UT-33", "Cubierta"],
        ["Selección: especial antes que por_hora, franja que cruza medianoche", "UT-31", "Cubierta"],
        ["Selección: salida fuera de la franja especial", "UT-32", "Cubierta"],
        ["Selección: franja especial dentro del mismo día (HORA_INICIO ≤ HORA_FIN)", "—", "No cubierta"],
        ["Selección: ninguna tarifa activa → «Sin Tarifa», $0", "UT-34", "Cubierta"],
        ["Cálculo por_hora (y su mínimo de 1 hora)", "UT-27, 28, 32, 33, 35", "Cubierta"],
        ["Cálculo por_fraccion", "UT-29", "Cubierta (sin el caso de 0 minutos)"],
        ["Cálculo plana_dia", "UT-30", "Cubierta (sin el caso de 0 minutos)"],
        ["Cálculo especial (valor fijo)", "UT-31", "Cubierta"],
    ]
    h.append(tabla([[p(x, "celda_enc" if i == 0 else "celda") for x in fila] for i, fila in enumerate(ramas_sp)], [9.4 * cm, 3.6 * cm, 4 * cm]))
    h.append(p("Quedan pendientes tres ramas menores: la franja especial diurna y el mínimo de 0 minutos en fracción y en día plano. "
               "Siguen la misma lógica que las ramas sí cubiertas y son las siguientes candidatas para ampliar la suite.", "nota"))
    h.append(p("Contexto: cobertura de toda la carpeta app/", "h2"))
    h.append(p(f"Medida sobre <i>app/</i> completo, la cobertura de líneas es <b>{resumen_global['Lines'][0]:.2f}%</b> "
               f"({resumen_global['Lines'][1]}/{resumen_global['Lines'][2]} líneas). La diferencia se explica porque <i>app/</i> incluye "
               "unas 3.000 líneas de componentes Livewire (las pantallas) y los modelos Eloquent, que solo declaran relaciones. Esos "
               "componentes mezclan presentación y flujo de interfaz, y su verificación corresponde a pruebas de componente o de "
               "extremo a extremo, no a pruebas unitarias. Por eso quedaron fuera del alcance de esta entrega, y ese alcance está "
               "declarado en <i>phpunit.xml</i>."))
    h.append(p("Hallazgo durante la construcción de la suite", "h2"))
    h.append(p("Al escribir las pruebas de <i>AuthService</i> se encontró un defecto real: <i>registrarUsuario</i> ignoraba el null que "
               "devuelve <i>SPHelper</i> cuando falla <i>sp_crear_usuario</i>, así que enviaba un código de verificación y reportaba el "
               "registro como exitoso aunque la cuenta no existiera. Primero se escribió la prueba UT-36 y se comprobó que fallaba. Después "
               "se corrigió el servicio para que lance una excepción antes de enviar el correo, y con eso la prueba pasa."))

    # ── Sección 4 ──
    h.append(PageBreak())
    h.append(p("4. Evidencias de ejecución", "h1"))
    h.append(p("Resumen del test runner", "h2"))
    entorno = [
        ["Comando", "php vendor/bin/phpunit --testdox --path-coverage --coverage-text --coverage-html … --coverage-clover … --log-junit …"],
        ["Entorno", "Windows 11 · PHP 8.4.28 (ZTS x64) · Xdebug 3.5.3 · PHPUnit 12.5.17 · Laravel 13.4.0 · MySQL 8.4.10"],
        ["Pruebas ejecutadas", f"{resumen_junit['tests']} ({n_pruebas} de este informe + 1 prueba de ejemplo de Laravel que ya existía en el repositorio: "
                               "«la portada responde 200»)"],
        ["Aserciones", resumen_junit["assertions"]],
        ["Fallos / errores / omitidas", f"{resumen_junit['failures']} / {resumen_junit['errors']} / {resumen_junit['skipped']}"],
        ["Tiempo total", f"{float(resumen_junit['time']):.2f} s (con instrumentación de cobertura de rutas activa)"],
        ["Resultado", f"<font color='#1E7B45'><b>OK — {n_pass}/{n_pruebas} pruebas del informe en PASS (100%)</b></font>"],
    ]
    h.append(tabla([[p(f"<b>{a}</b>", "celda"), p(str(b), "celda")] for a, b in entorno], [4 * cm, 13 * cm], encabezado=False))
    h.append(p("Archivos de evidencia en el repositorio: <i>docs/evidencias/salida_phpunit.txt</i> (log completo), "
               "<i>docs/evidencias/junit.xml</i> (resultados por prueba), <i>docs/cobertura/clover.xml</i> y "
               "<i>docs/cobertura/html/index.html</i> (reporte navegable, línea por línea).", "nota"))
    h.append(p("Log de consola (salida textual de PHPUnit)", "h2"))
    seguro = html.escape(log_consola).replace("✔", "<font name='Simbolos'>✔</font>").replace("✘", "<font name='Simbolos'>✘</font>")
    seguro = re.sub(r"(Configuration:\s+)\S.*", r"\1parkme/phpunit.xml", seguro)
    h.append(XPreformatted(seguro, E["codigo"]))

    doc.build(h)


def main() -> None:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--salida", type=Path, default=RAIZ / "Tarea_2_Pruebas_Proyecto_Integrador.pdf")
    args = ap.parse_args()
    construir(args.salida)
    print(f"PDF generado: {args.salida}")


if __name__ == "__main__":
    main()
