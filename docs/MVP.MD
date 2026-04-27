Perfeito — vamos **refatorar o MVP inteiro** para o nicho SaaS analytics.
Agora o projeto deixa de ser “analytics genérico” e vira **Conversational BI para métricas SaaS (MRR, churn, growth)**.

Isso muda principalmente:

* dataset
* perguntas suportadas
* narrativa do produto
* exemplos de SQL
* valor percebido

Abaixo está a **versão atualizada do arquivo do MVP** já adaptado 👇

---

# 🧠 Decisões técnicas importantes

## Banco: SQLite ou PostgreSQL?

👉 Continue usando **SQLite no MVP**.

Para este nicho ele funciona ainda melhor, porque:

* workload é analítico (SELECT pesado)
* dataset é simulado
* zero infra
* onboarding fácil para quem clonar o repo

📌 Estratégia profissional:

* MVP → SQLite
* README → “production-ready with PostgreSQL”

---

## Biblioteca de gráficos

👉 **Apache ECharts continua sendo a melhor escolha.**

Agora faz ainda mais sentido porque:

* dashboards SaaS usam gráficos densos
* métricas temporais (MRR growth, churn)
* multi-series charts

Isso reforça narrativa de produto SaaS.

---

# 🎯 Escopo do MVP (versão 2 – SaaS Analytics)

O MVP agora responde perguntas sobre **métricas de assinatura**.

O sistema deve conseguir responder perguntas como:

* Qual é nosso MRR?
* Como está o crescimento de receita?
* Quantos usuários ativos temos?
* Qual o churn deste mês?
* Qual plano gera mais receita?
* Quantas novas assinaturas por mês?

E retornar:

* tabela
* gráfico automático
* resumo executivo

Sem login. Sem multi-tenant.
Foco: **motor de analytics conversacional para SaaS**.

---

# 🧩 Arquitetura do MVP

Fluxo permanece igual:

```
User → Chat → Controller → Action → Agent → Tool → DB → Response → Vue Charts
```

---

# 🗄️ Dataset do sistema (SaaS Company)

Agora simulamos **uma startup SaaS**.

## Tabelas principais

### companies (clientes do SaaS)

* id
* name
* created_at

### users (usuários finais do produto)

* id
* company_id
* email
* created_at
* cancelled_at (nullable)

### subscription_plans

* id
* name (Starter, Pro, Enterprise)
* price_monthly

### subscriptions

* id
* user_id
* plan_id
* started_at
* cancelled_at (nullable)

### invoices (pagamentos)

* id
* user_id
* amount
* status (paid, refunded)
* paid_at

🔥 Este schema permite calcular métricas reais:

* MRR
* churn
* growth rate
* active users
* revenue by plan
* new subscriptions
* lifetime value (básico)

Agora o agente conversa com métricas reais de negócio.

---

# 📂 Estrutura de pastas (Laravel + Actions Pattern)

Permanece igual:

```
app/
 ├── Actions/
 │    └── Analytics/
 │         ├── AskAnalyticsAction.php
 │         ├── GenerateSqlAction.php
 │         ├── RunQueryAction.php
 │         ├── GenerateChartSpecAction.php
 │         └── GenerateSummaryAction.php
 │
 ├── AI/
 │    ├── Tools/
 │    │     └── RunAnalyticsQueryTool.php
 │    └── Prompts/
 │          └── analytics-agent.txt
 │
 ├── Services/
 │    └── SchemaContextService.php
```

---

# 🔄 Fluxo completo do request

## Controller

```
POST /analytics/ask
```

Chama:

```
AskAnalyticsAction::run($question);
```

---

## Action Orquestradora

Pipeline continua:

```php
$sql = GenerateSqlAction::run($question);
$data = RunQueryAction::run($sql);
$chart = GenerateChartSpecAction::run($question, $data);
$summary = GenerateSummaryAction::run($question, $data);

return compact('data','chart','summary');
```

---

# 🤖 O Agente SQL (atualizado)

Agora ele precisa entender **métricas SaaS**.

Ele recebe:

* pergunta do usuário
* schema do banco
* regras de segurança

E retorna SQL válido.

---

## SchemaContextService (novo contexto)

Agora retorna algo como:

```
Tables:

companies(id, name, created_at)

users(id, company_id, email, created_at, cancelled_at)

subscription_plans(id, name, price_monthly)

subscriptions(id, user_id, plan_id, started_at, cancelled_at)

invoices(id, user_id, amount, status, paid_at)
```

---

## Novo exemplo de SQL esperado

Pergunta:

> Show MRR growth by month

Exemplo de saída:

```sql
SELECT 
  strftime('%Y-%m', paid_at) AS month,
  SUM(amount) AS mrr
FROM invoices
WHERE status = 'paid'
GROUP BY month
ORDER BY month;
```

Agora parece BI real.

---

# 🔐 Tool de SQL segura

Sem mudanças:

Validações obrigatórias:

* apenas SELECT
* limite de linhas
* timeout

---

# 📊 Geração automática de gráficos

Sem mudanças estruturais.

Exemplo de saída:

```json
{
 "type": "line",
 "xField": "month",
 "yField": "mrr",
 "title": "MRR Growth"
}
```

---

# 🎨 Estrutura do Frontend (Inertia + Vue)

Permanece:

```
Pages/Analytics/Index.vue
```

Layout:

```
| Chat | Chart |
|------|-------|
| msgs | graph |
```

---

# 💬 Exemplo real de resposta do sistema

Pergunta:

> How is our MRR evolving?

Resposta:

Resumo:

> MRR increased 22% over the last 6 months.

Gráfico:

* linha de crescimento mensal

Tabela:

* dados crus

Agora isso parece ferramenta para founder.

---

# 🧪 Perguntas demo (ATUALIZADAS)

Coloque no README:

* Show MRR by month
* How many active users do we have?
* What is the churn rate this month?
* Which plan generates the most revenue?
* New subscriptions per month
* Revenue growth over time

Essas perguntas vendem o produto.

---

# 🚀 Roadmap pós-MVP

Depois você pode evoluir com:

* memória de conversa
* dashboards salvos
* alertas automáticos
* comparação entre períodos

Mas não entram agora.

---

# 🏁 Resultado final do MVP

Você estará construindo:

> Um copiloto conversacional de métricas SaaS que transforma perguntas em SQL, gráficos e insights executivos.

Isso é MUITO mais forte que analytics genérico.
