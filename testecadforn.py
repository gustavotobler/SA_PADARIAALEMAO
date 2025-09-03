from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import random, time
from datetime import datetime, timedelta

# --- Funções para gerar dados válidos ---
def gerar_cnpj():
    base = [random.randint(0,9) for _ in range(8)] + [0,0,0,1]
    def dv(numeros):
        soma = sum([(len(numeros)+1-i)*v for i,v in enumerate(numeros)])
        resto = soma % 11
        return 0 if resto < 2 else 11-resto
    d1 = dv(base)
    d2 = dv(base+[d1])
    return "{}{}.{:03d}.{:03d}/{:04d}-{:02d}".format(
        base[0],base[1], int("".join(map(str,base[2:5]))),
        int("".join(map(str,base[5:8]))),
        int("".join(map(str,base[8:12]))),
        int(f"{d1}{d2}")
    )

def gerar_telefone():
    return f"(47) 9{random.randint(8000,9999)}-{random.randint(1000,9999)}"

def gerar_cep():
    return f"{random.randint(10000,99999)}-{random.randint(100,999)}"

def gerar_nome_empresa():
    empresas = ["Padaria Sol", "Doces da Serra", "Moinho Real", "Delícias do Pão", "Cereal Sul"]
    return random.choice(empresas)

def gerar_email():
    return f"fornecedor{random.randint(1,999)}@teste.com"

def gerar_data_fundacao():
    inicio = datetime.now() - timedelta(days=365*80)
    fim = datetime.now() - timedelta(days=365*5)
    dt = inicio + (fim - inicio) * random.random()
    return dt.strftime("%Y-%m-%d")

# --- Configuração Selenium ---
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 10)

# --- Login ---
driver.get("http://localhost:8080/SA_PADARIAALEMAO/index.php")
time.sleep(1)  # pausa
wait.until(EC.presence_of_element_located((By.ID, "Email"))).send_keys("kerryking@padaria.com")
time.sleep(0.5)
driver.find_element(By.ID, "Senha").send_keys("admin123")
time.sleep(0.5)
driver.find_element(By.XPATH, "//button[text()='Entrar']").click()
time.sleep(2)  # deixa a página inicial carregar

# --- Acessar Fornecedores ---
forn_btn = wait.until(EC.element_to_be_clickable(
    (By.XPATH, "//a[contains(@href,'fornecedores.php')]")
))
driver.execute_script("arguments[0].scrollIntoView(true);", forn_btn)
time.sleep(0.5)
forn_btn.click()
time.sleep(2)  # esperar fornecedores.php carregar

# --- Esperar div actions-top ---
wait.until(EC.presence_of_element_located((By.CLASS_NAME, "actions-top")))
time.sleep(0.5)

# --- Habilitar botões ocultos (ícone lápis) ---
edit_icon = wait.until(EC.element_to_be_clickable((By.ID, "edit-toggle")))
edit_icon.click()
time.sleep(1)

# --- Clicar no botão de adicionar fornecedor (+) ---
add_btn = wait.until(EC.element_to_be_clickable((By.ID, "add-button")))
add_btn.click()
time.sleep(2)  # esperar cadforn.php carregar

# --- Preenchendo campos ---
driver.find_element(By.ID, "nome_forn").send_keys(gerar_nome_empresa())
time.sleep(0.5)
driver.find_element(By.ID, "cnpj").send_keys(gerar_cnpj())
time.sleep(0.5)

campo_data = driver.find_element(By.ID, "data_fundacao")
campo_data.clear()
campo_data.send_keys(gerar_data_fundacao())
time.sleep(0.5)

driver.find_element(By.ID, "logradouro").send_keys("Rua Central")
time.sleep(0.3)
driver.find_element(By.ID, "num_empresa").send_keys(str(random.randint(1,999)))
time.sleep(0.3)
driver.find_element(By.ID, "bairro").send_keys("Centro")
time.sleep(0.3)
driver.find_element(By.ID, "cidade").send_keys("Joinville")
time.sleep(0.3)
driver.find_element(By.ID, "uf").send_keys("SC")
time.sleep(0.3)
driver.find_element(By.ID, "cep").send_keys(gerar_cep())
time.sleep(0.3)
driver.find_element(By.ID, "email").send_keys(gerar_email())
time.sleep(0.3)
driver.find_element(By.ID, "telefone").send_keys(gerar_telefone())
time.sleep(0.5)

# --- Submeter formulário ---
wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[type='submit']"))).click()
time.sleep(3)  # pausa final para ver resultado

driver.quit()
