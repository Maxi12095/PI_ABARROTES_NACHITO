from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time

driver = webdriver.Chrome()

# URL de la aplicación en XAMPP
url = "http://localhost/login.php"

# Abrir la página de login
driver.get(url)

# Esperar un poco para que la página cargue
time.sleep(2)

# Encontrar los campos de usuario y contraseña (ajusta si los nombres de los inputs son diferentes)
usuario_input = driver.find_element(By.NAME, "username")  # Ajusta si el campo tiene otro atributo NAME
password_input = driver.find_element(By.NAME, "password")  # Ajusta si el campo tiene otro atributo NAME

# Ingresar las credenciales de prueba
usuario_input.send_keys("artyom")
password_input.send_keys("1234")

# Enviar el formulario
password_input.send_keys(Keys.RETURN)

time.sleep(3)

# Verificar si el login fue exitoso
if "dashboard" in driver.current_url:  # Ajusta "dashboard" si la URL cambia tras iniciar sesión
    print("Login exitoso")
else:
    print("Error en el login")

# Cerrar el navegador
driver.quit()
