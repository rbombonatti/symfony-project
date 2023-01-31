# Symfony Project
### Data: 25/01/2023<br /><br />

### Etapas
1) A criação de uma rota que encontra um hash, de certo formato, para uma certa string fornecida como input. <br />
2) A criação de um comando que consulta a rota criada e armazena os resultados na base de dados.<br />
3) Criação de uma rota que retorne os resultados que foram gravados<br />

### Comandos iniciais

1) php -S localhost:8083 -t public


### Rotas e Comandos
1) Listagem de Hashes: `http://localhost:8083/hashes/` <br />
2) Geração Hash Individual: `http://localhost:8083/hashes/create/{STRING DE ENTRADA}/{QUANTIDADE DE REQUISIÇÕES}`<br />
3) Comando Symfony: `php bin/console generate-hashes {STRING DE ENTRADA} {QUANTIDADE DE REQUISIÇÕES}`<br />
4) Listagem completa via SQL: `php bin/console doctrine:query:sql "SELECT * FROM Hashes"` <br />


### Models & Controllers
1) Hashes & HashesController

### Database
SQLite 

### Migrations
- Version20230129173622.php

### Comentários
#### Etapa 1
* Os métodos para a criação das Hashes estão no controller principal (HashesController) seguindo o Single Responsibility Principle; <br />
* Para a limitação das requisições foi utilizado o Rate Limiter (https://symfony.com/doc/current/rate_limiter.html) e configurado em config/packages/rate_limiter.yaml<br />
* Entidade, migrations e controller criados a partir do comando **make** <br />

#### Etapa 2
* Comando Symfony criado a partir de make:command <br />
* A regra de negócio descrita no item 2 acontece no método generateHashCascate();

#### Etapa 3
* Listagem de Hashes criadas em **http://localhost:8083/hashes/**, ordenadas pela coluna blockNumber
