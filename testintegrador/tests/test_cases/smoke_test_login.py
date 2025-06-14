import os
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from utils.report_login import generar_pdf
from utils.gmail import enviar_correo

def smoke_test_login():
    # Configuración de Chrome
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    resultados = []
    capturas = []
    url = "http://localhost:3000/php/iniciar_sesion.php"
    
    # --- AQUI VA EL BLOQUE SOLICITADO ---
    test_name = "login"
    screenshot_dir = os.path.join("tests", "screenshots", test_name)
    os.makedirs(screenshot_dir, exist_ok=True)
    # -------------------------------------

    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    try:
        # 1. Acceso a la URL
        driver.get(url)
        resultados.append(f"Accedió a la URL: {driver.current_url}")
        time.sleep(2)
        
        # Captura de pantalla inicial
        captura1 = os.path.join(screenshot_dir, "captura1_inicio.png")
        driver.save_screenshot(captura1)
        capturas.append(captura1)
        
        # 2. Verificación de elementos del formulario
        resultados.append("\nVERIFICACIÓN DE ELEMENTOS DEL FORMULARIO:")
        
        campos = {
            "Email (solo Gmail)": "email",
            "Contraseña": "contrasena",
        }
        
        for label, name in campos.items():
            try:
                input_element = driver.find_element(By.NAME, name)
                resultados.append(f"V Campo '{label}' encontrado")
            except:
                resultados.append(f"F Campo '{label}' NO encontrado")
        
        try:
            btn_submit = driver.find_element(By.XPATH, "//button[@type='submit']")
            resultados.append("\nV Botón de envío encontrado")
        except:
            resultados.append("\nF Botón de envío NO encontrado")
        
        # Captura de pantalla final
        captura2 = "tests/screenshots/captura_final.png"
        driver.save_screenshot(captura2)
        capturas.append(captura2)
        
        resultados.append("\nPrueba completada exitosamente")
        
    except Exception as e:
        resultados.append(f"\nError durante el testeo: {str(e)}")
        captura_error = "tests/screenshots/error.png"
        driver.save_screenshot(captura_error)
        capturas.append(captura_error)
        raise
    finally:
        driver.quit()
    
    duracion = round(time.time() - start_time, 2)

    # Generar reporte PDF
    os.makedirs("tests/reports", exist_ok=True)
    pdf_filename = f"tests/reports/reporte_smoke_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf"
    generar_pdf("\n".join(resultados), capturas, pdf_filename, duracion, fecha_hora)
    
    # Enviar correo con mensaje mejorado
    asunto = "Reporte de Smoke Test - Iniciar Sesión - Login"
    cuerpo = f"""
    Este es el testeo de creación de Inicio de sesión - Login.
    
    Se adjunta el reporte con los resultados detallados y capturas de pantalla.
    
    Resumen ejecución:
    - Fecha: {fecha_hora}
    - Duración: {duracion} segundos
    - Campos verificados: {len(campos)}
    
    """
    
    exito, mensaje = enviar_correo(asunto, cuerpo, pdf_filename)
    if not exito:
        print(f"Error al enviar correo: {mensaje}")
    
    return "\n".join(resultados), duracion, fecha_hora