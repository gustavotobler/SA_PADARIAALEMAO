from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import random
import time
from datetime import datetime, timedelta

# --- Funções para gerar dados ---
def gerar_nome_produto():
    produtos = ["Pão Francês", "Bolo Chocolate", "Croissant", "Pão Integral", "Biscoito", "Torta"]
    return random.choice(produtos)

def gerar_preco():
    return f"R$ {random.randint(1,50)},{random.randint(0,99):02d}"

def gerar_unidade():
    return random.choice(["kg","g","mL"])

def gerar_validade():
    dt = datetime.now() + timedelta(days=random.randint(1,180))
    return dt.strftime("%d/%m/%Y")

def gerar_quantidade():
    return random.randint(1,100)

# --- Configuração Selenium ---
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 10)

# --- Login ---
driver.get("http://localhost:8080/SA_PADARIAALEMAO/index.php")
wait.until(EC.presence_of_element_located((By.ID, "Email"))).send_keys("kerryking@padaria.com")
driver.find_element(By.ID, "Senha").send_keys("admin123")
driver.find_element(By.XPATH, "//button[text()='Entrar']").click()

# --- Acessar Produtos ---
prod_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[@href='produtos.php']")))
prod_btn.click()

# --- Mostrar botões e clicar em "add" ---
wait.until(EC.presence_of_element_located((By.ID, "edit-toggle"))).click()
time.sleep(1)  # pausa maior
add_btn = wait.until(EC.element_to_be_clickable((By.ID, "add-button")))
add_btn.click()

# --- Espera cadproduto.php carregar ---
wait.until(EC.presence_of_element_located((By.ID, "Nome_prod")))

# --- Função digitar bem devagar ---
def digitar_com_mascara(elemento, valor, delay=0.15):  # 150ms entre cada tecla
    elemento.clear()
    for c in valor:
        elemento.send_keys(c)
        time.sleep(delay)
    time.sleep(0.3)  # pausa pequena após preencher o campo

# --- Preenchendo formulário ---
# Fornecedor e categoria
forn_select = Select(wait.until(EC.presence_of_element_located((By.ID, "ID_forn"))))
if len(forn_select.options) > 1:
    forn_select.select_by_index(random.randint(1, len(forn_select.options)-1))
time.sleep(0.3)

cat_select = Select(wait.until(EC.presence_of_element_located((By.ID, "id_categorias"))))
if len(cat_select.options) > 1:
    cat_select.select_by_index(random.randint(1, len(cat_select.options)-1))
time.sleep(0.3)

# Nome
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Nome_prod"))), gerar_nome_produto(), delay=0.2)
# Preço
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Preco_unitario"))), gerar_preco(), delay=0.2)
# Unidade
unid_select = Select(wait.until(EC.presence_of_element_located((By.ID, "Unid_medida"))))
unid_select.select_by_visible_text(gerar_unidade())
time.sleep(0.3)
# Validade
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Validade"))), gerar_validade(), delay=0.2)
# Quantidade
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Qntd_produto"))), str(gerar_quantidade()), delay=0.2)

# --- Submeter formulário ---
wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[type='submit']"))).click()

# --- Pausa final para ver resultado ---
time.sleep(5)
driver.quit()
