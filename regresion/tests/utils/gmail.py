import smtplib
from email.message import EmailMessage
import os

# Configuración de Gmail
GMAIL_USER = "lpze.manuelarturo.paye.ca@unifranz.edu.bo"
GMAIL_PASS = "aqbw ayzc oqvd opyh"
DESTINO = "manuelpaye.13@gmail.com"

def enviar_correo(asunto, cuerpo, archivo_pdf):
    """Envía un correo con el reporte PDF adjunto"""
    
    # Validar que exista el archivo PDF
    if not os.path.exists(archivo_pdf):
        return False, f"El archivo PDF {archivo_pdf} no existe"
    
    try:
        msg = EmailMessage()
        msg["From"] = GMAIL_USER
        msg["To"] = DESTINO
        msg["Subject"] = asunto
        
        # Versión texto plano
        msg.set_content(cuerpo)
        
        # Versión HTML (opcional)
        msg.add_alternative(f"""\
        <html>
          <body style="font-family: Arial, sans-serif; line-height: 1.6;">
            <h2 style="color: #2d3748;">{asunto}</h2>
            <div style="margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 5px;">
              {cuerpo.replace('\n', '<br>')}
            </div>
            <p style="font-size: 0.9em; color: #718096;">
              Este es un mensaje automático, por favor no responder.
            </p>
          </body>
        </html>
        """, subtype='html')
        
        # Adjuntar PDF
        with open(archivo_pdf, "rb") as f:
            file_data = f.read()
            msg.add_attachment(
                file_data,
                maintype="application",
                subtype="pdf",
                filename=os.path.basename(archivo_pdf)
            )
        
        # Enviar correo
        with smtplib.SMTP_SSL("smtp.gmail.com", 465) as smtp:
            smtp.login(GMAIL_USER, GMAIL_PASS)
            smtp.send_message(msg)
        
        return True, "Correo enviado correctamente"
    
    except smtplib.SMTPAuthenticationError:
        error_msg = "Error de autenticación. Verifica tus credenciales y configuración de Gmail"
        return False, error_msg
    except Exception as e:
        return False, f"Error al enviar correo: {str(e)}"