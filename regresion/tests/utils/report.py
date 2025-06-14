import os
from fpdf import FPDF

class PDFReport(FPDF):
    def header(self):
        # Logo o título
        self.set_font('Arial', 'B', 16)
        self.cell(0, 10, 'Reporte de Pruebas Automatizadas', 0, 1, 'C')
        self.set_font('Arial', '', 10)
        self.cell(0, 5, 'Pruebas de Regresión - Módulo de Login', 0, 1, 'C')
        self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', 'I', 8)
        self.cell(0, 10, f'Página {self.page_no()}', 0, 0, 'C')

def generar_pdf(texto, lista_capturas, nombre_archivo, duracion, fecha_hora):
    pdf = PDFReport()
    pdf.add_page()
    pdf.set_auto_page_break(auto=True, margin=15)
    
    # Estilo para el reporte
    pdf.set_font("Arial", '', 11)
    
    # Encabezado con información general
    pdf.set_fill_color(240, 240, 240)
    pdf.cell(0, 8, f'Fecha y Hora: {fecha_hora}', 0, 1, 'L', 1)
    pdf.cell(0, 8, f'Duración de la prueba: {duracion} segundos', 0, 1, 'L', 1)
    pdf.ln(5)
    
    # Procesar el texto con formato mejorado
    pdf.set_font("Arial", '', 10)
    line_height = 6
    page_width = pdf.w - 2 * pdf.l_margin
    
    # Dividir el texto en secciones
    sections = texto.split('\n\n')
    
    for section in sections:
        if not section.strip():
            pdf.ln(line_height)
            continue
            
        # Verificar si es un encabezado de sección
        if section.startswith("VERIFICACIÓN") or section.startswith("PRUEBAS FUNCIONALES"):
            pdf.set_font("Arial", 'B', 12)
            pdf.cell(0, line_height, section, 0, 1)
            pdf.set_font("Arial", '', 10)
            pdf.ln(2)
        elif section.startswith("Caso"):
            pdf.set_font("Arial", 'B', 10)
            lines = section.split('\n')
            pdf.cell(0, line_height, lines[0], 0, 1)
            pdf.set_font("Arial", '', 10)
            for line in lines[1:]:
                if line.strip():
                    # Procesar líneas con ✓ y ✗
                    if line.startswith("✓"):
                        pdf.set_text_color(0, 128, 0)  # Verde
                    elif line.startswith("✗"):
                        pdf.set_text_color(255, 0, 0)  # Rojo
                    
                    pdf.multi_cell(page_width, line_height, line)
                    pdf.set_text_color(0, 0, 0)  # Negro
            pdf.ln(2)
        else:
            # Texto normal
            lines = section.split('\n')
            for line in lines:
                if line.strip():
                    pdf.multi_cell(page_width, line_height, line)
            pdf.ln(2)
    
    # Añadir capturas de pantalla con mejor formato
    if lista_capturas:
        pdf.add_page()
        pdf.set_font("Arial", 'B', 12)
        pdf.cell(0, 10, 'Capturas de Pantalla', 0, 1)
        pdf.ln(5)
        
        for img_path in lista_capturas:
            if os.path.exists(img_path):
                try:
                    # Ajustar tamaño de imagen manteniendo proporción
                    pdf.image(img_path, x=10, w=pdf.w - 20)
                    pdf.ln(5)
                    pdf.set_font("Arial", 'I', 8)
                    pdf.cell(0, 5, os.path.basename(img_path), 0, 1)
                    pdf.ln(10)
                    pdf.set_font("Arial", '', 10)
                except Exception as e:
                    pdf.set_font("Arial", 'I', 10)
                    pdf.cell(0, 5, f"No se pudo cargar la imagen: {os.path.basename(img_path)}", 0, 1)
            else:
                pdf.set_font("Arial", 'I', 10)
                pdf.cell(0, 5, f"Imagen no encontrada: {img_path}", 0, 1)
    
    # Guardar el PDF
    pdf.output(nombre_archivo)