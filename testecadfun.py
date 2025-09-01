from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import random
from datetime import datetime, timedelta
import time

# --- Funções para gerar dados válidos ---
def gerar_cpf():
    def mod11(num):
        soma = sum([(len(num)+1-i)*int(x) for i,x in enumerate(num)])
        resto = soma % 11
        return '0' if resto < 2 else str(11-resto)
    n = [str(random.randint(0,9)) for _ in range(9)]
    n.append(mod11(n))
    n.append(mod11(n))
    return ''.join(n)

def gerar_rg():
    return ''.join([str(random.randint(0,9)) for _ in range(9)])

def gerar_telefone():
    return f"11{random.randint(900000000, 999999999)}"

def gerar_data_nascimento():
    inicio = datetime.now() - timedelta(days=365*50)
    fim = datetime.now() - timedelta(days=365*18)
    dt = inicio + (fim - inicio) * random.random()
    return dt.strftime("%d/%m/%Y")

def gerar_data_admissao():
    inicio = datetime.now() - timedelta(days=365*5)
    fim = datetime.now()
    dt = inicio + (fim - inicio) * random.random()
    return dt.strftime("%d/%m/%Y")

def gerar_nome():
    nomes = ["João", "Maria", "Carlos", "Ana", "Lucas", "Beatriz"]
    sobrenomes = ["Silva", "Souza", "Oliveira", "Pereira", "Costa", "Almeida"]
    return f"{random.choice(nomes)} {random.choice(sobrenomes)}"

def gerar_cep():
    return f"{random.randint(10000, 99999)}{random.randint(100, 999)}"

def gerar_cidade():
    cidade = [
        "Araquari","Barra do Sul","Garuva",
        "Guaramirim","Itapoá","Jaraguá do Sul",
        "Massaranduba","Schroeder","São Francisco do Sul"
    ]
    return random.choice(cidade)


def gerar_bairro():
    bairro  = [
        "Centro","Atiradores","Bucarein",
        "Floresta","Glória","Iririú",
        "Saguaçu","Costa e Silva","Anita Garibaldi",
        "Boa Vista","Comasa","Cidade Nova",
        "Parque Guarani","América","Bom Retiro",
        "Itaum","Nova Brasília","Zona Industrial Norte"
    ]
    return random.choice(bairro)

def gerar_logradouro():
    ruas = [
        "Rua das Palmeiras","Rua XV de Novembro","Rua 7 de Setembro",
        "Rua Visconde de Taunay","Rua Benjamin Constant","Rua dos Estados",
        "Rua Nove de Março","Rua Iririú","Rua São Paulo","Rua Almirante Barroso",
        "Rua Amazonas","Rua das Flores","Rua do Príncipe","Rua Anita Garibaldi",
        "Rua das Nações"
    ]
    return random.choice(ruas)


# --- Configuração Selenium ---
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 10)

# --- Login ---
driver.get("http://localhost:8080/SA_PADARIAALEMAO/index.php")
wait.until(EC.presence_of_element_located((By.ID, "Email"))).send_keys("kerryking@padaria.com")
driver.find_element(By.ID, "Senha").send_keys("admin123")
driver.find_element(By.XPATH, "//button[text()='Entrar']").click()

# --- Acessar Funcionários ---
func_btn = wait.until(EC.element_to_be_clickable((By.LINK_TEXT, "🙎‍♂️ Funcionários")))
func_btn.click()

# --- Esperar funcionarios.php carregar ---
wait.until(EC.presence_of_element_located((By.ID, "edit-toggle")))

# --- Mostrar botões e clicar no "add" ---
lapis = driver.find_element(By.ID, "edit-toggle")
lapis.click()
time.sleep(0.5)  # delay para animação
add_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "#add-button button.add-btn")))
driver.execute_script("arguments[0].click();", add_btn)

# --- Espera cadfunc.php carregar ---
wait.until(EC.presence_of_element_located((By.ID, "Nome_func")))

# --- Função para digitar devagar (para máscaras JS) ---
def digitar_com_mascara(elemento, valor):
    elemento.clear()
    for c in valor:
        elemento.send_keys(c)
        time.sleep(0.05)

# --- Preenchendo campos ---
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Nome_func"))), gerar_nome())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "telefone"))), gerar_telefone())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "rg"))), gerar_rg())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "cpf"))), gerar_cpf())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "cep"))), gerar_cep())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "UF"))), "SC")
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Num_casa"))), str(random.randint(1,500)))
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Cidade"))), gerar_cidade())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Bairro"))), gerar_bairro())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Logradouro"))), gerar_logradouro())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "email"))), f"teste{random.randint(1,999)}@email.com")
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "senha"))), "senha1234")
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "nascimento"))), gerar_data_nascimento())
digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "admissao"))), gerar_data_admissao())

# --- Selecionando opções ---
Select(wait.until(EC.presence_of_element_located((By.ID, "Sexo")))).select_by_value(random.choice(["M","F"]))
Select(wait.until(EC.presence_of_element_located((By.ID, "Esta_civil")))).select_by_visible_text(random.choice(["Solteiro","Casado","Viúvo"]))
Select(wait.until(EC.presence_of_element_located((By.ID, "nivel_de_acesso")))).select_by_value(random.choice(["1","2"]))
Select(wait.until(EC.presence_of_element_located((By.ID, "cargo")))).select_by_visible_text(random.choice(["Gerente","Padeiro","Caixa","Confeiteiro"]))

# --- Submeter formulário ---
wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[type='submit']"))).click()

# Pausa para ver resultado
time.sleep(5)
driver.quit()
