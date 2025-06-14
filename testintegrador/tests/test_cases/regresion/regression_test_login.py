import os
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from utils.regression_login import generar_pdf
from utils.gmail import enviar_correo

def regression_test_login():
    # Configuración de Chrome
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")

    driver = webdriver.Chrome(options=options)
    resultados = []
    capturas = []
    tabla_resultados = []
    url = "http://localhost:3000/php/iniciar_sesion.php"

    test_name = "regresion_login"
    screenshot_dir = os.path.join("tests", "screenshots", test_name)
    os.makedirs(screenshot_dir, exist_ok=True)

    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    try:
        driver.get(url)
        time.sleep(1)
        captura1 = os.path.join(screenshot_dir, "inicio.png")
        driver.save_screenshot(captura1)
        capturas.append(captura1)

        # 01: Login con datos correctos
        driver.find_element(By.NAME, "email").send_keys("admin@gmail.com")
        driver.find_element(By.NAME, "contrasena").send_keys("123456")
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        time.sleep(2)
        actual_url = driver.current_url
        estado = "V" if "admin/index.php" in actual_url or "inicio.php" in actual_url else "F"
        resultado_real = "Acceso correcto" if estado == "V" else "Acceso fallido"
        tabla_resultados.append(["01", "Login con datos correctos", "Acceso al sistema", resultado_real, estado])
        captura2 = os.path.join(screenshot_dir, "login_correcto.png")
        driver.save_screenshot(captura2)
        capturas.append(captura2)

        # 02: Login con datos incorrectos
        driver.get(url)
        time.sleep(1)
        driver.find_element(By.NAME, "email").send_keys("incorrecto@gmail.com")
        driver.find_element(By.NAME, "contrasena").send_keys("wrongpass")
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        time.sleep(2)
        try:
            mensaje_error = driver.find_element(By.CLASS_NAME, "error-message").text
            estado = "V" if "incorrectos" in mensaje_error else "F"
            resultado_real = "Mensaje correcto" if estado == "V" else "Mensaje inesperado"
        except:
            estado = "F"
            resultado_real = "Sin mensaje de error"
        tabla_resultados.append(["02", "Login con datos incorrectos", "Mensaje de error", resultado_real, estado])
        captura3 = os.path.join(screenshot_dir, "login_incorrecto.png")
        driver.save_screenshot(captura3)
        capturas.append(captura3)

        # 03: Ir al carrito sin login
        driver.get("http://localhost:3000/php/carrito.php")
        time.sleep(2)
        actual_url = driver.current_url
        estado = "V" if "iniciar_sesion.php" in actual_url else "F"
        resultado_real = "Redirigido al login" if estado == "V" else "Página en blanco o acceso no controlado"
        tabla_resultados.append(["03", "Ir al carrito sin login", "Redirigir al login", resultado_real, estado])
        captura4 = os.path.join(screenshot_dir, "sin_login_carrito.png")
        driver.save_screenshot(captura4)
        capturas.append(captura4)

    except Exception as e:
        resultados.append(f"Error en prueba: {str(e)}")
        captura_error = os.path.join(screenshot_dir, "error.png")
        driver.save_screenshot(captura_error)
        capturas.append(captura_error)
    finally:
        driver.quit()

    duracion = round(time.time() - start_time, 2)

    # Construir tabla como string para PDF o consola
    tabla = "| ID | Caso de prueba              | Resultado esperado | Resultado real                   | Estado |\n"
    tabla += "|----|-----------------------------|--------------------|----------------------------------|--------|\n"
    for fila in tabla_resultados:
        tabla += f"| {fila[0]} | {fila[1]:<27} | {fila[2]:<18} | {fila[3]:<32} | {fila[4]} |\n"

    resultados.append("RESULTADOS DE PRUEBA DE REGRESIÓN:\n")
    resultados.append(tabla)

    # Generar PDF
    os.makedirs("tests/reports", exist_ok=True)
    pdf_filename = f"tests/reports/reporte_regresion_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf"
    generar_pdf(tabla, capturas, pdf_filename, duracion, fecha_hora)

    # Enviar correo
    asunto = "Reporte de Prueba de Regresión - Login"
    cuerpo = f"""
    Este es el test de regresión de la funcionalidad Login.

    Se adjunta el reporte con capturas y resultados en tabla.

    Fecha: {fecha_hora}
    Duración: {duracion} segundos
    """

    exito, mensaje = enviar_correo(asunto, cuerpo, pdf_filename)
    if not exito:
        print(f"Error al enviar correo: {mensaje}")

    return tabla, duracion, fecha_hora
