
## Instruções de uso
Aplicação desenvolvida com o framework MVC Laravel. Banco de Dados MySQL.


O diagrama ER com o modelo de dados da aplicação está dentro da pasta img (imgs\diagrama_er.png).

## URLS

1. Obter moedas - http://api-laravel.test/obter_moedas \
    Método: GET \
    Não é necessário passar parâmetros

2. Obter cotação de compra e venda das moedas - http://api-laravel.test/cotacao \
    Método: GET \
    Não é necessário passar parâmetros


3. Inserir moeda BRL no banco de dados - http://api-laravel.test/insere_brl \
    Método: GET \
    Não é necessário passar parâmetros


4. Criar nova conta - http://apilaravel.test/api/criar_conta \
	Método: POST \
    Parâmetros:
    * numero - deve ser um número postivo menor que 10000


5. Deposito - POST - http://api-laravel.test/api/deposito \
    Método: POST \
    Parâmetros:
	* c_numero - dever ser o número de uma conta já existente no banco de dados
	* m_simbolo - deve ser um das siglas das moedas
disponibilizadas pela API do Banco Central, no formato definito pela ISO 4217
   * saldo - refre-se ao valor a ser depositado. Dever ser um número positivo.


6. Saque - http://api-laravel.test/api/sacar \
    Método: POST \
    Parâmetros:
	* c_numero - dever ser o número de uma conta já existente no banco de dados
	* m_simbolo - deve ser um das siglas das moedas
disponibilizadas pela API do Banco Central, no formato definito pela ISO 4217
   * saldo - refre-se ao valor a ser sacado. Dever ser um número positivo.


7. Consultar saldo - http://api-laravel.test/api/ver_saldo \
    Método: POST \
    Parâmetros:
	* c_numero - dever ser o número de uma conta já existente no banco de dados
	* m_simbolo (opcional) - deve ser um das siglas das moedas
disponibilizadas pela API do Banco Central, no formato definito pela ISO 4217.




