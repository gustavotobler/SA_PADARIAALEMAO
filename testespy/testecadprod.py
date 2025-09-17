from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
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
    return f"R$ {random.randint(1, 50)},{random.randint(0, 99):02d}"

def gerar_unidade():
    return random.choice(["kg", "g", "mL"])

def gerar_data_fundacao(formato="%Y-%m-%d"):
    data_inicio = datetime(1800, 1, 1)
    data_fim = datetime.now()
    delta = data_fim - data_inicio
    dias_aleatorios = random.randint(0, delta.days)
    data_aleatoria = data_inicio + timedelta(days=dias_aleatorios)
    data_formatada = data_aleatoria.strftime(formato)
    print(f"Data de fundação gerada: {data_formatada}")
    return data_formatada

def gerar_validade(formato="%Y-%m-%d"):
    data_inicio = datetime.now()
    data_fim = data_inicio + timedelta(days=365)  # até 1 ano no futuro
    delta = data_fim - data_inicio
    dias_aleatorios = random.randint(0, delta.days)
    data_aleatoria = data_inicio + timedelta(days=dias_aleatorios)
    data_formatada = data_aleatoria.strftime(formato)
    print(f"Data de validade gerada (ISO): {data_formatada}")
    return data_formatada

def gerar_quantidade():
    return random.randint(1, 100)

# --- Função utilitária para formatar de acordo com o campo ---
def formatar_para_campo(validade_iso, campo):
    """
    Recebe validade_iso no formato YYYY-MM-DD e tenta retornar o formato esperado
    pelo campo (por exemplo DD/MM/YYYY) com base em atributos do próprio elemento.
    """
    placeholder = (campo.get_attribute("placeholder") or "").lower()
    tipo = (campo.get_attribute("type") or "").lower()
    data_mask = (campo.get_attribute("data-mask") or "").lower()
    data_format = (campo.get_attribute("data-date-format") or "").lower()

    # Se for input type=date, mantém ISO (YYYY-MM-DD)
    if tipo == "date":
        return validade_iso

    # Se houver indicação explícita de formato dd/mm/yyyy
    if "dd/mm" in placeholder or "dd/mm" in data_mask or "dd/mm/yyyy" in data_format or "dd-mm-yyyy" in data_format:
        dt = datetime.strptime(validade_iso, "%Y-%m-%d")
        return dt.strftime("%d/%m/%Y")

    # Se placeholder contém barras, usar dd/mm/yyyy como heurística
    if "/" in placeholder:
        try:
            dt = datetime.strptime(validade_iso, "%Y-%m-%d")
            return dt.strftime("%d/%m/%Y")
        except:
            pass

    # Se máscara com hífens (ex:  dd-mm-yyyy)
    if "-" in placeholder or "-" in data_mask:
        dt = datetime.strptime(validade_iso, "%Y-%m-%d")
        return dt.strftime("%d-%m-%Y")

    # Fallback: retorna ISO
    return validade_iso

# --- Configuração Selenium ---
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 20)

try:
    # --- Login ---
    print("Acessando a página de login...")
    driver.get("http://localhost:8080/SA_PADARIAALEMAO/index.php")
    time.sleep(3)
    wait.until(EC.presence_of_element_located((By.ID, "Email"))).send_keys("kerryking@padaria.com")
    time.sleep(1)
    driver.find_element(By.ID, "Senha").send_keys("12345678")
    time.sleep(1)
    driver.find_element(By.XPATH, "//button[text()='Entrar']").click()
    time.sleep(5)
    print("Login realizado.")

    # --- Acessar Produtos ---
    print("Acessando página de Produtos...")
    prod_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[@href='estoque.php']")))
    prod_btn.click()
    time.sleep(3)
    print("Página de Produtos aberta.")

    # --- Mostrar botões e clicar em 'add' ---
    print("Abrindo formulário de adição...")
    wait.until(EC.presence_of_element_located((By.ID, "openCreateBtn"))).click()
    time.sleep(3)
    print("Formulário de adição aberto.")

    # --- Espera cadproduto.php carregar ---
    wait.until(EC.presence_of_element_located((By.ID, "Nome_prod")))
    time.sleep(2)
    print("Formulário carregado.")

    # --- Função digitar bem devagar ---
    def digitar_com_mascara(elemento, valor, delay=0.15):
        elemento.clear()
        for c in valor:
            elemento.send_keys(c)
            time.sleep(delay)
        time.sleep(0.5)
        print(f"Campo preenchido com: {valor}")
        valor_atual = elemento.get_attribute("value")
        print(f"Valor atual no campo: {valor_atual}")
        return valor_atual

    # --- Preenchendo formulário ---
    # Fornecedor
    forn_select = Select(wait.until(EC.presence_of_element_located((By.ID, "ID_forn"))))
    if len(forn_select.options) > 1:
        forn_select.select_by_index(random.randint(1, len(forn_select.options) - 1))
    time.sleep(1)
    print("Fornecedor selecionado.")

    # Categoria
    cat_select = Select(wait.until(EC.presence_of_element_located((By.ID, "id_categorias"))))
    if len(cat_select.options) > 1:
        cat_select.select_by_index(random.randint(1, len(cat_select.options) - 1))
    time.sleep(1)
    print("Categoria selecionada.")

    # Nome
    digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Nome_prod"))), gerar_nome_produto(), delay=0.2)
    time.sleep(1)

    # Preço
    digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Preco_unitario"))), gerar_preco(), delay=0.2)
    time.sleep(1)

    # Unidade
    unid_select = Select(wait.until(EC.presence_of_element_located((By.ID, "Unid_medida"))))
    unid_select.select_by_visible_text(gerar_unidade())
    time.sleep(1)

    # ---------- VALIDADE (bloco corrigido) ----------
    validade_iso = gerar_validade(formato="%Y-%m-%d")
    validade_field = wait.until(EC.presence_of_element_located((By.ID, "Validade")))
    time.sleep(0.2)

    # Determina o formato que vamos enviar com base no campo
    formatted = formatar_para_campo(validade_iso, validade_field)
    print(f"Formato escolhido para envio de Validade: {formatted} (campo type/placeholder: {validade_field.get_attribute('type')}, {validade_field.get_attribute('placeholder')})")

    # 1) Tentar Ctrl+A + send_keys (substituir tudo de uma vez)
    try:
        validade_field.click()
        # Clear + selecionar tudo para garantir substituição
        validade_field.send_keys(Keys.CONTROL, "a")
        time.sleep(0.05)
        validade_field.send_keys(formatted)
        time.sleep(0.4)
        valor_validade = validade_field.get_attribute("value")
        print(f"Tentativa 1 - Valor preenchido em Validade: {valor_validade}")
    except Exception as e:
        print("Tentativa 1 falhou:", e)
        valor_validade = validade_field.get_attribute("value")

    # 2) Se não bate, setar via JavaScript e disparar eventos
    if not valor_validade or (valor_validade.strip() != formatted.strip()):
        try:
            js_val = formatted
            script = """
            var el = arguments[0];
            var val = arguments[1];
            // focus e set value
            el.focus();
            // se for input date, atribui Date para garantir compatibilidade (opcional)
            try {
                el.value = val;
            } catch(e) {
                el.setAttribute('value', val);
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            """
            driver.execute_script(script, validade_field, js_val)
            time.sleep(0.3)
            valor_validade = validade_field.get_attribute("value")
            print(f"Tentativa 2 (JS) - Valor após JS: {valor_validade}")
        except Exception as e:
            print("Tentativa 2 falhou:", e)

    # 3) Último recurso: digitar caractere-a-caractere garantindo caret no final
    if not valor_validade or (valor_validade.strip() != formatted.strip()):
        try:
            validade_field.clear()
            for c in formatted:
                validade_field.send_keys(c)
                # tenta garantir que caret vá para o fim
                validade_field.send_keys(Keys.END)
                time.sleep(0.03)
            time.sleep(0.3)
            valor_validade = validade_field.get_attribute("value")
            print(f"Tentativa 3 - Valor final: {valor_validade}")
        except Exception as e:
            print("Tentativa 3 falhou:", e)

    # Log final e verificação
    if valor_validade is None:
        valor_validade = ""
    if valor_validade.strip() != formatted.strip():
        print(f"AVISO: valor esperado '{formatted}' mas encontrado '{valor_validade}'. Salve a página (page source) para depurar.")
        # salvar para análise
        with open("erro_validade_debug.html", "w", encoding="utf-8") as f:
            f.write(driver.page_source)
        print("Fonte da página salva em 'erro_validade_debug.html'.")
    else:
        print("Validade preenchida corretamente.")

    # Quantidade
    digitar_com_mascara(wait.until(EC.presence_of_element_located((By.ID, "Qntd_produto"))), str(gerar_quantidade()), delay=0.2)
    time.sleep(1)

    # --- Submeter formulário ---
    print("Submetendo formulário...")
    submit_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[type='submit']")))
    submit_btn.click()
    time.sleep(5)
    print("Formulário submetido.")

    # --- Verificar resultado ---
    time.sleep(5)
    print("Pausa final para verificar resultado.")

except Exception as e:
    print(f"Erro ocorrido: {str(e)}")
    with open("erro_debug.html", "w", encoding="utf-8") as f:
        f.write(driver.page_source)
    print("Fonte da página salva em 'erro_debug.html' para análise.")

finally:
    driver.quit()
