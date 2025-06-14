from flask import Flask, render_template_string, request
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
import time
import os
from fpdf import FPDF
from datetime import datetime

import smtplib
from email.message import EmailMessage

app = Flask(__name__)

HTML_TEMPLATE = """
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smoke Test Selenium</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 800px;
            width: 90%;
            margin: 2rem;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #4a5568;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .header p {
            color: #718096;
            font-size: 1.1rem;
        }
        
        .test-form {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .btn-test {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .btn-test:active {
            transform: translateY(0);
        }
        
        .btn-test:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 2rem 0;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: #667eea;
            font-size: 1.2rem;
            font-weight: 500;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }
        
        .results {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid #48bb78;
        }
        
        .results h2 {
            color: #2d3748;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .results h2::before {
            content: "✅";
            margin-right: 0.5rem;
        }
        
        .results pre {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
            color: #2d3748;
            white-space: pre-wrap;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .info-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .info-card strong {
            color: #4a5568;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-card span {
            display: block;
            color: #667eea;
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #48bb78;
        }
        
        .icon {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-test {
                padding: 0.8rem 2rem;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Smoke Test Selenium</h1>
            <p>Pruebas automatizadas para Creación de Habitaciones</p>
        </div>
        
        <form method="POST" class="test-form" id="testForm">
            <button type="submit" class="btn-test" id="testBtn">
                <span class="icon">🚀</span>
                Iniciar Testeo
            </button>
        </form>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <div class="loading-text">Ejecutando pruebas... Por favor espere</div>
        </div>
        
        {% if resultado %}
        <div class="results">
            <h2>Resultado del Testeo</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <strong>Duración</strong>
                    <span>{{ duracion }}s</span>
                </div>
                <div class="info-card">
                    <strong>Fecha y Hora</strong>
                    <span>{{ fecha_hora }}</span>
                </div>
            </div>
            
            <pre>{{ resultado }}</pre>
            
            <div class="success-message">
                <span class="icon">📧</span>
                <strong>¡Reporte enviado!</strong> Se envió un correo electrónico con el reporte detallado y las capturas de pantalla.
            </div>
        </div>
        {% endif %}
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('testBtn');
            const loading = document.getElementById('loading');
            
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<span class="icon">⏳</span>Testeando...';
            loading.classList.add('show');
            
            // Scroll suave hacia el loading
            loading.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        
        // Si hay resultados, hacer scroll suave hacia ellos
        {% if resultado %}
        window.addEventListener('load', function() {
            const results = document.querySelector('.results');
            if (results) {
                setTimeout(() => {
                    results.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 500);
            }
        });
        {% endif %}
    </script>
</body>
</html>
"""

# Configura aquí tu cuenta Gmail para enviar el correo
GMAIL_USER = "lpze.manuelarturo.paye.ca@unifranz.edu.bo"
GMAIL_PASS = "aqbw ayzc oqvd opyh"

# Destinatario
DESTINO = "manuelpaye.13@gmail.com"

def enviar_correo(asunto, cuerpo, archivo_pdf):
    msg = EmailMessage()
    msg["From"] = GMAIL_USER
    msg["To"] = DESTINO
    msg["Subject"] = asunto
    msg.set_content(cuerpo)
    
    with open(archivo_pdf, "rb") as f:
        file_data = f.read()
        file_name = os.path.basename(archivo_pdf)
    
    msg.add_attachment(file_data, maintype="application", subtype="pdf", filename=file_name)
    
    try:
        with smtplib.SMTP_SSL("smtp.gmail.com", 465) as smtp:
            smtp.login(GMAIL_USER, GMAIL_PASS)
            smtp.send_message(msg)
        print("Correo enviado correctamente.")
    except Exception as e:
        print(f"Error enviando correo: {e}")

def generar_pdf(texto_resultados, capturas, archivo_salida, duracion, fecha_hora):
    pdf = FPDF()
    pdf.set_auto_page_break(auto=True, margin=15)
    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.cell(0, 10, "Reporte de Smoke Test - Creación de Habitaciones", ln=True, align="C")
    pdf.ln(10)
    
    pdf.set_font("Arial", size=12)
    pdf.cell(0, 10, f"Fecha y hora del test: {fecha_hora}", ln=True)
    pdf.cell(0, 10, f"Duración del test: {duracion} segundos", ln=True)
    pdf.ln(5)
    
    pdf.multi_cell(0, 8, texto_resultados)
    pdf.ln(10)
    
    for captura in capturas:
        pdf.add_page()
        pdf.image(captura, w=180)
    
    pdf.output(archivo_salida)

def smoke_test():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    
    driver = webdriver.Chrome(options=options)
    resultados = []
    capturas = []
    url = "http://localhost:3000/admin/crearhabitacion/habitaciones.php?action=create"
    
    start_time = time.time()
    fecha_hora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    try:
        driver.get(url)
        resultados.append(f"Accedió a la URL: {driver.current_url}")
        time.sleep(2)
        
        # Captura de pantalla inicial
        captura1 = "captura1.png"
        driver.save_screenshot(captura1)
        capturas.append(captura1)
        
        # Buscar formulario
        try:
            form = driver.find_element(By.TAG_NAME, "form")
            resultados.append("Formulario encontrado correctamente.")
        except:
            resultados.append("Formulario NO encontrado.")
        
        campos = {
            "Número de habitación": "numero",
            "Tipo de habitación": "tipo_habitacion_id",
            "Precio por noche": "precio_por_noche",
            "Estado": "estado",
            "capacidad": "capacidad",
            "Piso": "piso_habitacion_id"
        }
        
        for label, name in campos.items():
            try:
                input_element = driver.find_element(By.NAME, name)
                resultados.append(f"V Campo '{label}' encontrado.")
            except:
                resultados.append(f"F Campo '{label}' NO encontrado.")
        
        try:
            btn_submit = driver.find_element(By.XPATH, "//button[@type='submit']")
            resultados.append("V Botón de envío encontrado.")
        except:
            resultados.append("F Botón de envío NO encontrado.")
        
        # Captura de pantalla final
        captura2 = "captura2.png"
        driver.save_screenshot(captura2)
        capturas.append(captura2)
        
    except Exception as e:
        resultados.append(f"Error durante el testeo: {str(e)}")
    finally:
        driver.quit()
    
    duracion = round(time.time() - start_time, 2)

    # Crear PDF con fecha/hora en el nombre
    pdf_filename = f"reporte_smoke_test_{datetime.now().strftime('%Y%m%d_%H%M%S')}.pdf"
    generar_pdf("\n".join(resultados), capturas, pdf_filename, duracion, fecha_hora)
    
    # Enviar correo
    asunto = "Reporte Smoke Test - Creación de Habitaciones"
    cuerpo = "Este es el testeo de creación de habitaciones. Adjuntamos el reporte con capturas."
    enviar_correo(asunto, cuerpo, pdf_filename)
    
    # Eliminar capturas y PDF (opcional)
    for f in capturas + [pdf_filename]:
        if os.path.exists(f):
            os.remove(f)
    
    return "\n".join(resultados), duracion, fecha_hora

@app.route("/", methods=["GET", "POST"])
def index():
    resultado = None
    duracion = None
    fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test()
    return render_template_string(HTML_TEMPLATE, resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

if __name__ == "__main__":
    app.run(debug=True)