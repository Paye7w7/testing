import os
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from utils.regression_habitacion import generar_pdf
from utils.gmail import enviar_correo

def regression_test_habitacion():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")

    driver = webdriver.Chrome(options=options)
    resultados = []
    capturas = []
    test_name = "habitacion_regression"
    screenshot_dir = os.path.join("tests", "screenshots", test_name)
    os.makedirs(screenshot_dir, exist_ok=True)

    url = "http://localhost:3000/admin/crearhabitacion/habitaciones.php?action=create"
    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    try:
        # Paso 1: Abrir formulario de creación
        driver.get(url)
        resultados.append("Accedió a formulario de creación")
        time.sleep(2)
        capturas.append(os.path.join(screenshot_dir, "inicio.png"))
        driver.save_screenshot(capturas[-1])

        # Paso 2: Rellenar formulario
        driver.find_element(By.NAME, "numero").send_keys("9999")
        Select(driver.find_element(By.NAME, "tipo_habitacion_id")).select_by_index(1)
        driver.find_element(By.NAME, "precio_por_noche").send_keys("100")
        Select(driver.find_element(By.NAME, "estado")).select_by_visible_text("Disponible")
        driver.find_element(By.NAME, "capacidad").send_keys("3")
        Select(driver.find_element(By.NAME, "piso_habitacion_id")).select_by_index(1)

        # Adjuntar imagen (obligatorio en creación)
        image_path = os.path.abspath("tests/data/test_room.jpg")  # asegúrate de tener esta imagen
        driver.find_element(By.NAME, "imagen").send_keys(image_path)
        time.sleep(1)

        capturas.append(os.path.join(screenshot_dir, "formulario_rellenado.png"))
        driver.save_screenshot(capturas[-1])

        # Paso 3: Enviar formulario
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        time.sleep(3)
        resultados.append("Formulario enviado correctamente")

        capturas.append(os.path.join(screenshot_dir, "despues_creacion.png"))
        driver.save_screenshot(capturas[-1])

        # Paso 4: Ir a listado y buscar la habitación recién creada
        list_url = "http://localhost:3000/admin/crearhabitacion/habitaciones.php?action=list"
        driver.get(list_url)
        time.sleep(2)

        resultados.append("Verificando que la habitación fue creada")
        page_source = driver.page_source
        if "9999" in page_source:
            resultados.append("V Habitación '9999' fue creada y está en el listado")
        else:
            resultados.append("F Habitación '9999' NO encontrada en el listado")

        capturas.append(os.path.join(screenshot_dir, "listado.png"))
        driver.save_screenshot(capturas[-1])

        # Paso 5: Eliminar la habitación creada (busca el botón eliminar por el número o posición)
        try:
            delete_button = driver.find_element(By.XPATH, f"//tr[td[contains(text(),'9999')]]//form//button")
            delete_button.click()
            time.sleep(2)
            resultados.append("Habitación eliminada exitosamente")
        except:
            resultados.append("F No se pudo eliminar la habitación")

        capturas.append(os.path.join(screenshot_dir, "eliminacion.png"))
        driver.save_screenshot(capturas[-1])

    except Exception as e:
        resultados.append(f"F Error inesperado: {str(e)}")
        captura_error = os.path.join(screenshot_dir, "error.png")
        driver.save_screenshot(captura_error)
        capturas.append(captura_error)

    finally:
        driver.quit()

    duracion = round(time.time() - start_time, 2)
    pdf_filename = f"tests/reports/regression_habitacion_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf"
    generar_pdf("\n".join(resultados), capturas, pdf_filename, duracion, fecha_hora)

    asunto = "Reporte de Regression Test - Habitaciones"
    cuerpo = f"""
    Este es el test de regresión para creación de habitaciones.

    - Fecha: {fecha_hora}
    - Duración: {duracion} segundos
    - Resultado: {'Éxito' if 'F' not in ''.join(resultados) else 'Fallas detectadas'}

    Se adjunta el PDF con evidencia.
    """

    exito, mensaje = enviar_correo(asunto, cuerpo, pdf_filename)
    if not exito:
        print(f"Error al enviar correo: {mensaje}")

    return "\n".join(resultados), duracion, fecha_hora
