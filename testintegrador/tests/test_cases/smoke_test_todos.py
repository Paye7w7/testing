import os
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from utils.report_todos import generar_pdf
from utils.gmail import enviar_correo

# Importar todos los tests individuales
from tests.test_cases.smoke_test_habitacion import smoke_test_habitacion
from tests.test_cases.smoke_test_hotel import smoke_test_hotel
from tests.test_cases.smoke_test_huesped import smoke_test_huesped
from tests.test_cases.smoke_test_login import smoke_test_login
from tests.test_cases.smoke_test_piso import smoke_test_piso
from tests.test_cases.smoke_test_reserva import smoke_test_reserva
from tests.test_cases.smoke_test_servicio import smoke_test_servicio

def ejecutar_y_capturar_test(nombre_test, test_func):
    """Ejecuta un test individual y captura sus resultados"""
    resultados = []
    capturas = []
    
    try:
        # Crear directorio específico para este test
        test_screenshot_dir = os.path.join("tests", "screenshots", nombre_test.lower())
        os.makedirs(test_screenshot_dir, exist_ok=True)
        
        # Limpiar capturas anteriores de este test
        for file in os.listdir(test_screenshot_dir):
            if file.endswith(".png"):
                os.remove(os.path.join(test_screenshot_dir, file))
        
        # Ejecutar el test
        test_resultados, duracion, _ = test_func()
        
        # Procesar resultados
        resultados.append(f"\n=== {nombre_test.upper()} ===")
        resultados.append(test_resultados)
        resultados.append(f"\nDuración: {duracion} segundos")
        
        # Recoger capturas específicas de este test
        capturas_test = [
            os.path.join(test_screenshot_dir, f) 
            for f in os.listdir(test_screenshot_dir)
            if f.endswith('.png')
        ]
        
        # Ordenar capturas por nombre
        capturas_test.sort()
        
        # Agregar descripción de cada captura
        for i, captura in enumerate(capturas_test, 1):
            resultados.append(f"\nCaptura {i}: {os.path.basename(captura)}")
        
        return "\n".join(resultados), capturas_test, True
    
    except Exception as e:
        error_msg = f"\nError en test {nombre_test}: {str(e)}"
        return error_msg, [], False

def smoke_test_todos():
    """Función principal que ejecuta todos los tests y genera un único reporte"""
    
    # Configuración inicial
    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    # Lista de todos los tests a ejecutar
    tests = [
        ("Habitacion", smoke_test_habitacion),
        ("Hotel", smoke_test_hotel),
        ("Huesped", smoke_test_huesped),
        ("Login", smoke_test_login),
        ("Piso", smoke_test_piso),
        ("Reserva", smoke_test_reserva),
        ("Servicio", smoke_test_servicio)
    ]
    
    # Resultados consolidados
    resultados_totales = []
    capturas_totales = []
    tests_exitosos = 0
    
    # Encabezado del reporte
    resultados_totales.append("REPORTE COMPLETO DE SMOKE TESTS")
    resultados_totales.append(f"Fecha y hora de ejecución: {fecha_hora}")
    resultados_totales.append("\n" + "="*50 + "\n")
    
    # Ejecutar cada test
    for nombre_test, test_func in tests:
        resultado, capturas, exito = ejecutar_y_capturar_test(nombre_test, test_func)
        resultados_totales.append(resultado)
        capturas_totales.extend(capturas)
        if exito:
            tests_exitosos += 1
        resultados_totales.append("\n" + "="*50 + "\n")
    
    # Generar reporte consolidado
    duracion_total = round(time.time() - start_time, 2)
    
    # Resumen ejecutivo
    resumen_ejecutivo = f"""
    RESUMEN EJECUTIVO:
    
    Fecha y hora: {fecha_hora}
    Duración total: {duracion_total} segundos
    Tests ejecutados: {len(tests)}
    Tests exitosos: {tests_exitosos}
    Tests fallidos: {len(tests) - tests_exitosos}
    """
    
    resultados_totales.append(resumen_ejecutivo)
    
    # Generar PDF único con todos los resultados
    reports_dir = os.path.join("tests", "reports")
    os.makedirs(reports_dir, exist_ok=True)
    pdf_filename = os.path.join(reports_dir, f"reporte_completo_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf")
    
    generar_pdf("\n".join(resultados_totales), capturas_totales, pdf_filename, duracion_total, fecha_hora)
    
    # Enviar un único correo con todo el contenido
    asunto = "Reporte Completo de Smoke Tests"
    cuerpo = f"""
    {resumen_ejecutivo}
    
    Detalles de los tests realizados:
    - Creación de Habitación
    - Registro de Hotel
    - Registro de Huésped
    - Inicio de Sesión
    - Creación de Piso
    - Reserva (Check-in/out)
    - Creación de Servicio
    
    Se adjunta el reporte PDF con todos los detalles y capturas de pantalla organizadas por test.
    """
    
    exito, mensaje = enviar_correo(asunto, cuerpo, pdf_filename)
    if not exito:
        print(f"Error al enviar correo: {mensaje}")
    
    return "\n".join(resultados_totales), duracion_total, fecha_hora