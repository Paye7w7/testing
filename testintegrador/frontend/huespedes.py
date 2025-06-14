from flask import Flask, render_template, request
from tests.test_cases.smoke_test_huesped import smoke_test_huesped
import os

app = Flask(__name__, template_folder='templates')

@app.route("/", methods=["GET", "POST"])
def index():
    resultado = None
    duracion = None
    fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_huesped()
    return render_template('huesped.html', resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

if __name__ == "__main__":
    app.run(debug=True)