from flask import Flask, render_template, request
from tests.test_cases.smoke_test_habitacion import smoke_test_habitacion
from tests.test_cases.smoke_test_hotel import smoke_test_hotel
from tests.test_cases.smoke_test_huesped import smoke_test_huesped
from tests.test_cases.smoke_test_login import smoke_test_login
from tests.test_cases.smoke_test_piso import smoke_test_piso
from tests.test_cases.smoke_test_reserva import smoke_test_reserva
from tests.test_cases.smoke_test_servicio import smoke_test_servicio
from tests.test_cases.smoke_test_todos import smoke_test_todos

app = Flask(__name__, template_folder='templates')

@app.route("/")
def index():
    test_routes = [
        {"name": "Habitación", "path": "/habitacion"},
        {"name": "Hoteles", "path": "/hoteles"},
        {"name": "Huéspedes", "path": "/huespedes"},
        {"name": "Login", "path": "/login"},
        {"name": "Piso", "path": "/piso"},
        {"name": "Reserva", "path": "/reserva"},
        {"name": "Servicio", "path": "/servicio"},
        {"name": "Todos", "path": "/todos"},
    ]
    return render_template("index.html", test_routes=test_routes)

@app.route("/habitacion", methods=["GET", "POST"])
def test_habitacion():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_habitacion()
    return render_template("habitacion.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/hoteles", methods=["GET", "POST"])
def test_hoteles():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_hotel()
    return render_template("hoteles.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/huespedes", methods=["GET", "POST"])
def test_huespedes():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_huesped()
    return render_template("huesped.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/login", methods=["GET", "POST"])
def test_login():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_login()
    return render_template("login.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/piso", methods=["GET", "POST"])
def test_piso():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_piso()
    return render_template("piso.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/reserva", methods=["GET", "POST"])
def test_reserva():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_reserva()
    return render_template("reserva.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/servicio", methods=["GET", "POST"])
def test_servicio():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_servicio()
    return render_template("servicio.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

@app.route("/todos", methods=["GET", "POST"])
def test_todos():
    resultado = duracion = fecha_hora = None
    if request.method == "POST":
        resultado, duracion, fecha_hora = smoke_test_todos()
    return render_template("todos.html", resultado=resultado, duracion=duracion, fecha_hora=fecha_hora)

if __name__ == "__main__":
    app.run(debug=True)
