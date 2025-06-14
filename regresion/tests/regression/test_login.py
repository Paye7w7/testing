import os
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from tests.utils.report import generar_pdf
from tests.utils.gmail import enviar_correo

def regression_test_login():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    wait = WebDriverWait(driver, 10)
    
    resultados = []
    capturas = []
    url = "http://localhost:3000/php/iniciar_sesion.php"
    
    test_name = "login_regression"
    screenshot_dir = os.path.join("tests", "screenshots", test_name)
    os.makedirs(screenshot_dir, exist_ok=True)
    
    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    try:
        # 1. Acceso a la página
        driver.get(url)
        resultados.append(f"Accedió a la URL: {driver.current_url}")
        time.sleep(2)
        
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
                driver.find_element(By.NAME, name)
                resultados.append(f"V Campo '{label}' encontrado")
            except NoSuchElementException:
                resultados.append(f"F Campo '{label}' NO encontrado")
        
        try:
            driver.find_element(By.XPATH, "//button[@type='submit']")
            resultados.append("\nV Botón de envío encontrado")
        except NoSuchElementException:
            resultados.append("\nF Botón de envío NO encontrado")
        
        # 3. Pruebas funcionales
        resultados.append("\nPRUEBAS FUNCIONALES:")
        
        # Caso 1: Campos vacíos
        resultados.append("\nCaso 1: Intento de login con campos vacíos")
        try:
            submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
            submit_btn.click()
            
            # Esperar mensaje de error
            try:
                error_msg = wait.until(
                    EC.visibility_of_element_located((By.XPATH, "//div[contains(@class, 'error')]"))
                )
                resultados.append(f"V Mensaje de error mostrado: '{error_msg.text}'")
            except TimeoutException:
                resultados.append("F No se mostró mensaje de error para campos vacíos")
                captura_error = os.path.join(screenshot_dir, "error_campos_vacios.png")
                driver.save_screenshot(captura_error)
                capturas.append(captura_error)
        except Exception as e:
            resultados.append(f"F Error al probar campos vacíos: {str(e)}")
        
        # Caso 2: Email inválido
        resultados.append("\nCaso 2: Intento de login con email inválido")
        try:
            driver.get(url)  # Recargar página para limpiar formulario
            email_field = driver.find_element(By.NAME, "email")
            pass_field = driver.find_element(By.NAME, "contrasena")
            
            email_field.send_keys("email_invalido")
            pass_field.send_keys("alguna_contraseña")
            driver.find_element(By.XPATH, "//button[@type='submit']").click()
            
            try:
                error_msg = wait.until(
                    EC.visibility_of_element_located((By.XPATH, "//div[contains(@class, 'error')]")))
                resultados.append(f"V Mensaje de error mostrado: '{error_msg.text}'")
            except TimeoutException:
                resultados.append("F No se mostró mensaje de error para email inválido")
                captura_error = os.path.join(screenshot_dir, "error_email_invalido.png")
                driver.save_screenshot(captura_error)
                capturas.append(captura_error)
        except Exception as e:
            resultados.append(f"F Error al probar email inválido: {str(e)}")
        
        # Caso 3: Credenciales incorrectas
        resultados.append("\nCaso 3: Intento de login con credenciales incorrectas")
        try:
            driver.get(url)  # Recargar página para limpiar formulario
            email_field = driver.find_element(By.NAME, "email")
            pass_field = driver.find_element(By.NAME, "contrasena")
            
            email_field.send_keys("test@gmail.com")
            pass_field.send_keys("contraseña_incorrecta")
            driver.find_element(By.XPATH, "//button[@type='submit']").click()
            
            try:
                error_msg = wait.until(
                    EC.visibility_of_element_located((By.XPATH, "//div[contains(@class, 'error')]")))
                resultados.append(f"V Mensaje de error mostrado: '{error_msg.text}'")
            except TimeoutException:
                resultados.append("F No se mostró mensaje de error para credenciales incorrectas")
                captura_error = os.path.join(screenshot_dir, "error_credenciales.png")
                driver.save_screenshot(captura_error)
                capturas.append(captura_error)
        except Exception as e:
            resultados.append(f"F Error al probar credenciales incorrectas: {str(e)}")
        
        # Caso 4: Login exitoso (requiere credenciales válidas)
        resultados.append("\nCaso 4: Intento de login con credenciales válidas")
        try:
            driver.get(url)  # Recargar página para limpiar formulario
            email_field = driver.find_element(By.NAME, "email")
            pass_field = driver.find_element(By.NAME, "contrasena")
            
            # NOTA: Reemplaza con credenciales válidas de prueba
            email_valido = "test_valido@gmail.com"
            pass_valida = "contraseña_valida"
            
            email_field.send_keys(email_valido)
            pass_field.send_keys(pass_valida)
            driver.find_element(By.XPATH, "//button[@type='submit']").click()
            
            try:
                # Esperar redirección o elemento que indique login exitoso
                wait.until(EC.url_contains("dashboard") | EC.url_contains("inicio"))
                resultados.append(f"V Login exitoso, redirigido a: {driver.current_url}")
                
                # Verificar elementos de sesión iniciada
                try:
                    driver.find_element(By.XPATH, "//*[contains(text(), 'Bienvenido')]")
                    resultados.append("V Mensaje de bienvenida encontrado")
                except NoSuchElementException:
                    resultados.append("F No se encontró mensaje de bienvenida")
                
                captura_exito = os.path.join(screenshot_dir, "login_exitoso.png")
                driver.save_screenshot(captura_exito)
                capturas.append(captura_exito)
                
            except TimeoutException:
                resultados.append("F No se redirigió después del login")
                captura_error = os.path.join(screenshot_dir, "error_login_exitoso.png")
                driver.save_screenshot(captura_error)
                capturas.append(captura_error)
        except Exception as e:
            resultados.append(f"F Error al probar login exitoso: {str(e)}")
        
        captura_final = os.path.join(screenshot_dir, "captura_final.png")
        driver.save_screenshot(captura_final)
        capturas.append(captura_final)
        
        resultados.append("\nPrueba completada exitosamente")
    except Exception as e:
        resultados.append(f"\nError durante el testeo: {str(e)}")
        captura_error = os.path.join(screenshot_dir, "error.png")
        driver.save_screenshot(captura_error)
        capturas.append(captura_error)
        raise
    finally:
        driver.quit()
    
    duracion = round(time.time() - start_time, 2)
    
    # Generar PDF
    os.makedirs("tests/reports", exist_ok=True)
    pdf_filename = f"tests/reports/reporte_regresion_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf"
    generar_pdf("\n".join(resultados), capturas, pdf_filename, duracion, fecha_hora)
    
    # Enviar correo
    asunto = "Reporte de Test de Regresión - Login"
    cuerpo = f"""
    Se ha ejecutado el test de regresión de inicio de sesión - Login.
    
    Resumen:
    Fecha: {fecha_hora}
    Duración: {duracion} segundos
    Campos verificados: {len(campos)}
    Casos de prueba ejecutados: 4
    """
    exito, mensaje = enviar_correo(asunto, cuerpo, pdf_filename)
    if not exito:
        resultados.append(f"\nError al enviar correo: {mensaje}")
    
    return "\n".join(resultados), capturas, duracion, fecha_hora