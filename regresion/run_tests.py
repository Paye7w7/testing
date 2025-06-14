from flask import Flask, render_template, jsonify, request
import threading

app = Flask(__name__)

from tests.regression.test_login import regression_test_login

# Variable global para guardar resultados
test_resultados = None
test_capturas = None
test_duracion = None
test_fecha_hora = None
test_running = False

@app.route("/")
def index():
    return render_template("index.html")

@app.route("/run_test", methods=["POST"])
def run_test():
    global test_resultados, test_capturas, test_duracion, test_fecha_hora, test_running
    
    if test_running:
        return jsonify({"status": "running"})
    
    test_running = True
    
    def worker():
        global test_resultados, test_capturas, test_duracion, test_fecha_hora, test_running
        try:
            resultados, capturas, duracion, fecha_hora = regression_test_login()
            test_resultados = resultados
            test_capturas = capturas
            test_duracion = duracion
            test_fecha_hora = fecha_hora
        except Exception as e:
            test_resultados = f"Error ejecutando el test: {str(e)}"
            test_capturas = []
            test_duracion = 0
            test_fecha_hora = ""
        finally:
            test_running = False
    
    thread = threading.Thread(target=worker)
    thread.start()
    
    return jsonify({"status": "started"})

@app.route("/get_results")
def get_results():
    if test_running:
        return jsonify({"status": "running"})
    if test_resultados is None:
        return jsonify({"status": "not_started"})
    return jsonify({
        "status": "finished",
        "resultados": test_resultados,
        "capturas": test_capturas,
        "duracion": test_duracion,
        "fecha_hora": test_fecha_hora
    })

if __name__ == "__main__":
    app.run(debug=True)
