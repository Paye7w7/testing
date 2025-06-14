from fpdf import FPDF
import os
from datetime import datetime

def generar_pdf(texto_resultados, capturas, archivo_salida, duracion, fecha_hora):
    pdf = FPDF()
    pdf.set_auto_page_break(auto=True, margin=15)
    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.cell(0, 10, "Reporte de Smoke Test - Creación de Hoteles", ln=True, align="C")
    pdf.ln(10)
    
    pdf.set_font("Arial", size=12)
    pdf.cell(0, 10, f"Fecha y hora del test: {fecha_hora}", ln=True)
    pdf.cell(0, 10, f"Duración del test: {duracion} segundos", ln=True)
    pdf.ln(5)
    
    pdf.multi_cell(0, 8, texto_resultados)
    pdf.ln(10)
    
    for captura in capturas:
        if os.path.exists(captura):
            pdf.add_page()
            pdf.image(captura, w=180)
    
    pdf.output(archivo_salida)