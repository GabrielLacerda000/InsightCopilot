Perfeito — vamos atualizar o roadmap inteiro para o **InsightCopilot focado em métricas SaaS** (MRR, churn, crescimento, assinaturas).
Agora o roadmap deixa de falar de e-commerce e passa a refletir o produto real.

Vou manter o formato executável + entregáveis visíveis.

---

# 🗺️ Roadmap – InsightCopilot (SaaS Conversational BI)

## ⏱️ Visão geral realista

| Semana | Fase                       | Resultado visível               |
| ------ | -------------------------- | ------------------------------- |
| 1      | Base + dataset SaaS        | App rodando com dados realistas |
| 2      | SQL Agent                  | Perguntas → tabela              |
| 3      | Charts + insights          | Perguntas → gráficos + resumo   |
| 4      | Conversational UI + polish | Projeto pronto para portfólio   |

Se acelerar, dá pra fazer em 10–14 dias.

---

# 🧱 FASE 1 — Fundação SaaS (Dia 1–3)

Objetivo: deixar o app rodando com **dados realistas de assinaturas**.

Essa fase é subestimada — mas é o que vai fazer os gráficos parecerem reais.

---

## 1️⃣ Setup Laravel + Inertia + Vue

**Entregável:** rota `/analytics` abrindo página vazia.

Tarefas:

* Laravel 13
* Inertia v3 + Vue 3
* Tailwind (opcional, recomendado)
* Criar página `Analytics/Index.vue`

Resultado: app abre e navega.

---

## 2️⃣ Configurar SQLite

**Entregável:** app usando SQLite local.

Tarefas:

* criar `database.sqlite`
* configurar `.env`
* rodar migration inicial

Zero infra = onboarding fácil para quem clonar o repo.

---

## 3️⃣ Criar schema SaaS (core do projeto)

**Entregável:** migrations prontas.

Crie migrations para:

### companies

* id
* user_id
* name
* created_at

### customers

* id
* company_id
* email
* created_at
* cancelled_at

### subscription_plans

* id
* name
* price_monthly

### subscriptions

* id
* customer_id
* plan_id
* started_at
* cancelled_at

### invoices

* id
* customer_id
* subscription_id
* amount
* status (paid/refunded)
* paid_at

Agora o banco já suporta métricas SaaS reais.

---

## 4️⃣ Criar seed realista (CRÍTICO)

Este passo define o sucesso do agente.

**Entregável:** banco com 12 meses de histórico SaaS.

Seed deve gerar:

* 1 empresa SaaS
* 800–1500 usuários
* 3 planos (Starter / Pro / Enterprise)
* assinaturas mensais ao longo de 12 meses
* cancelamentos distribuídos (churn realista)
* invoices mensais recorrentes
* crescimento gradual de usuários

Simule:

* crescimento mês a mês
* alguns meses ruins
* upgrades/downgrades simples

📌 Sem isso, os gráficos ficam sem graça.

---

# 🤖 FASE 2 — Motor Conversational SQL (Dia 4–7)

Agora começa o coração do projeto.

---

## 5️⃣ Criar estrutura Actions Pattern

**Entregável:** pasta Analytics pronta.

Criar:

```
app/Actions/Analytics
```

Actions iniciais:

* AskAnalyticsAction
* GenerateSqlAction
* RunQueryAction

Ainda sem IA — só estrutura.

---

## 6️⃣ Criar SchemaContextService

**Entregável:** serviço que descreve o banco.

Ele deve retornar texto assim:

```
Tables:
users(id, company_id, created_at, cancelled_at)
subscriptions(id, user_id, plan_id, started_at, cancelled_at)
subscription_plans(id, name, price_monthly)
invoices(id, user_id, amount, status, paid_at)
```

Esse texto alimenta o agente.

---

## 7️⃣ Criar SQL Agent (Laravel AI SDK)

**Entregável:** pergunta → SQL.

Fluxo:
Controller → GenerateSqlAction → AI SDK → retorna SQL.

Testar perguntas:

* Show MRR by month
* Active users per month
* New subscriptions per month

⚠️ Ainda não executa query.

---

## 8️⃣ Criar QueryTool segura

**Entregável:** SQL executado com segurança.

Implementar:

* bloquear UPDATE/DELETE/INSERT
* limitar resultados
* timeout
* logs

Agora temos:

Pergunta → SQL → Executa → tabela.

🎉 PRIMEIRO GRANDE MARCO.

Você já tem BI conversacional funcionando.

---

# 📊 FASE 3 — Visualização de métricas (Dia 8–10)

Agora o projeto começa a parecer SaaS de verdade.

---

## 9️⃣ Instalar Apache ECharts no Vue

**Entregável:** componente de gráfico funcionando.

Criar:

```
components/ChartRenderer.vue
```

Testar com dados mock.

---

## 🔟 Criar GenerateChartSpecAction (IA)

**Entregável:** dados → config de gráfico.

Entrada:

* pergunta
* colunas retornadas

Saída exemplo:

```json
{
 "type": "line",
 "xField": "month",
 "yField": "mrr",
 "title": "MRR Growth"
}
```

---

## 11️⃣ Integrar gráficos com dados reais

**Entregável:** pergunta → gráfico real 🎉

Agora a demo começa a impressionar de verdade.

---

# ✍️ FASE 4 — Insights automáticos (Dia 11–12)

Aqui o projeto deixa de ser dashboard e vira **copiloto**.

---

## 12️⃣ Criar GenerateSummaryAction

Entrada:

* pergunta
* dados retornados

Saída:

* resumo executivo

Exemplo:

> MRR increased 21% over the last 6 months while churn decreased.

Esse detalhe muda totalmente a percepção do projeto.

---

## 13️⃣ Orquestrar pipeline completo

Finalizar `AskAnalyticsAction`.

Pipeline final:

1. gerar SQL
2. executar query
3. gerar gráfico
4. gerar resumo

**Entregável:** endpoint retorna resposta completa.

---

# 💬 FASE 5 — UI Conversacional (Dia 13)

Agora vira produto de verdade.

---

## 14️⃣ Criar Chat UI

Componentes:

* ChatBox
* Message
* Loading state

Histórico simples no frontend.

---

## 15️⃣ Criar layout SaaS

Tela final:

```
| Chat | Chart + Table + Summary |
```

Agora parece ferramenta usada por founder.

---

# ✨ FASE 6 — Polimento de portfólio (Dia 14)

Essa fase define impacto em entrevistas.

---

## 16️⃣ Criar README forte

Inclua:

* screenshots
* gif demo
* arquitetura
* decisões técnicas
* roadmap futuro

---

## 17️⃣ Perguntas demo (ATUALIZADAS)

Coloque no README:

Try asking:

* What is our MRR?
* Show MRR growth over time
* How many active users do we have?
* What is the churn this month?
* Which plan generates most revenue?
* New subscriptions per month

Essas perguntas vendem o produto instantaneamente.

---

# 🏁 Resultado final

Você terá um app que:

* conversa sobre métricas SaaS
* gera SQL automaticamente
* executa no banco
* cria gráficos automaticamente
* escreve insights executivos

Isso posiciona você como alguém que sabe integrar **IA + dados + produto** — algo raro no portfólio.
