from flask import Flask, render_template, request
from tests.test_cases.smoke_test_reserva import smoke_test_reserva
import os

app = Flask(__name__, template_folder='templates')

@app.route("/", methods=["GET", "POST"])
def index():
    resultado = None
    duracion = None
    fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_reserva()
    return render_template('reserva.html', resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

if __name__ == "__main__":
    app.run(debug=True)