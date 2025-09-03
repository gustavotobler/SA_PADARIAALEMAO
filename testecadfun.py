from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
import random
import time
from datetime import datetime, timedelta

# --- Função para digitar devagar (para máscaras JS) ---
def digitar_com_mascara(elemento, valor, delay=0.05):
    elemento.clear()
    for c in valor:
        elemento.send_keys(c)
        time.sleep(delay)

# --- Funções geradoras (CPF, RG, telefone, datas, nomes) ---
def gerar_cpf():
    def mod11(num):
        soma = sum((len(num)+1-i)*int(x) for i,x in enumerate(num))
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
    cidades = ["Araquari","Barra do Sul","Garuva","Guaramirim","Itapoá","Jaraguá do Sul",
               "Massaranduba","Schroeder","São Francisco do Sul"]
    return random.choice(cidades)

def gerar_bairro():
    bairros = ["Centro","Atiradores","Bucarein","Floresta","Glória","Iririú",
               "Saguaçu","Costa e Silva","Anita Garibaldi","Boa Vista","Comasa",
               "Cidade Nova","Parque Guarani","América","Bom Retiro","Itaum",
               "Nova Brasília","Zona Industrial Norte"]
    return random.choice(bairros)

def gerar_logradouro():
    ruas = ["Rua das Palmeiras","Rua XV de Novembro","Rua 7 de Setembro",
            "Rua Visconde de Taunay","Rua Benjamin Constant","Rua dos Estados",
            "Rua Nove de Março","Rua Iririú","Rua São Paulo","Rua Almirante Barroso",
            "Rua Amazonas","Rua das Flores","Rua do Príncipe","Rua Anita Garibaldi",
            "Rua das Nações"]
    return random.choice(ruas)

# --- Helper: selecionar select por texto ---
def selecionar_por_texto(driver, select_elem, texto):
    try:
        Select(select_elem).select_by_visible_text(texto)
        return True
    except:
        try:
            select_elem.click()
            opt = driver.find_element(By.XPATH, f"//option[normalize-space()='{texto}']")
            opt.click()
            return True
        except:
            return False

# --- Inicialização do driver ---
options = webdriver.ChromeOptions()
options.add_argument("--start-maximized")
service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=options)
wait = WebDriverWait(driver, 15)

try:
    # --- LOGIN ---
    wait.until(EC.visibility_of_element_located((By.ID, "Email"))).send_keys("kerryking@padaria.com")
    wait.until(EC.visibility_of_element_located((By.ID, "Senha"))).send_keys("admin123")
    driver.find_element(By.XPATH, "//button[normalize-space()='Entrar']").click()
    print("[Login] Realizado com sucesso")

    # --- NAVEGAR PARA FUNCIONÁRIOS ---
    func_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[contains(normalize-space(.), 'Funcionários')]")))
    func_btn.click()
    print("[Navegação] Página de Funcionários aberta")

    # --- MOSTRAR AÇÕES (ícone lápis) ---
    lapis = wait.until(EC.element_to_be_clickable((By.ID, "edit-toggle")))
    lapis.click()
    print("[Ações] Ícone lápis clicado")

    # --- CLICAR BOTÃO ADICIONAR (+) ---
    add_btn = None
    for _ in range(5):
        try:
            add_btn = wait.until(EC.element_to_be_clickable((By.ID, "add-button")))
            add_btn.click()
            print("[Ações] Botão + clicado")
            break
        except:
            print("[Retry] Botão + ainda não clicável, tentando novamente...")
            time.sleep(1)
    if not add_btn:
        raise Exception("Botão + não clicável")

    # --- ESPERAR FORMULÁRIO ---
    wait.until(EC.visibility_of_element_located((By.ID, "Nome_func")))
    print("[Formulário] Formulário carregado")

    # --- PREENCHIMENTO DE CAMPOS ---
    campos = [
        ("Nome_func", gerar_nome()),
        ("telefone", gerar_telefone()),
        ("rg", gerar_rg()),
        ("cpf", gerar_cpf()),
        ("cep", gerar_cep()),
        ("UF", "SC"),
        ("Num_casa", str(random.randint(1,500))),
        ("Cidade", gerar_cidade()),
        ("Bairro", gerar_bairro()),
        ("Logradouro", gerar_logradouro()),
        ("email", f"teste{random.randint(1000,9999)}@email.com"),
        ("senha", "senha1234"),
        ("nascimento", gerar_data_nascimento()),
        ("admissao", gerar_data_admissao())
    ]

    for campo_id, valor in campos:
        digitar_com_mascara(wait.until(EC.visibility_of_element_located((By.ID, campo_id))), valor)
        print(f"[Preenchimento] {campo_id} preenchido")

    # --- SELEÇÃO DE SELECTS ---
    sexo_elem = wait.until(EC.presence_of_element_located((By.ID, "Sexo")))
    selecionar_por_texto(driver, sexo_elem, random.choice(["Masculino", "Feminino"]))

    est_civil_elem = wait.until(EC.presence_of_element_located((By.ID, "Esta_civil")))
    selecionar_por_texto(driver, est_civil_elem, random.choice(["Solteiro", "Casado", "Viúvo"]))

    nivel_elem = wait.until(EC.presence_of_element_located((By.ID, "nivel_de_acesso")))
    Select(nivel_elem).select_by_value(random.choice(["1", "2"]))

    cargo_elem = wait.until(EC.presence_of_element_located((By.ID, "cargo")))
    selecionar_por_texto(driver, cargo_elem, random.choice(["Gerente", "Padeiro", "Caixa", "Confeiteiro"]))

    print("[Selects] Seleções realizadas")

    # --- SUBMIT ---
    wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[type='submit']"))).click()
    print("[Formulário] Submit clicado")

    # --- Esperar redirecionamento ou resultado ---
    wait.until(EC.visibility_of_element_located((By.ID, "edit-toggle")))
    print("[Sucesso] Cadastro concluído")

except Exception as e:
    print("[Erro]", e)

finally:
    driver.quit()
