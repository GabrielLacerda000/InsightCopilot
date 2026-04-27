# Analytics Chat — Fluxo de Mensagens

## Visão Geral

A página de Analytics (`/analytics`) funciona como um chat de IA. O usuário digita uma pergunta em linguagem natural, o backend gera e executa uma query SQL via um agente Gemini, e a resposta é exibida na tela com texto, SQL e tabela de dados.

---

## Fluxo Completo

```
Usuário digita → ChatInput emite "submit"
     ↓
Index.vue: handleSubmit(question)
     ↓
Mensagem do usuário + placeholder "loading" adicionados ao array `messages`
     ↓                               ↓
Vue renderiza balão               Vue renderiza três
amarelo imediatamente             pontinhos animados
     ↓
fetch POST /analytics/ask  { question }
     ↓
AnalyticsController::ask()
     ↓
AskAnalyticsAction::run($question)
     ↓
SqlGeneratorAgent (Gemini 2.5 Flash, max 5 steps)
  ├─ lê schema via SchemaContextService
  ├─ gera SELECT query
  └─ executa via RunQueryTool → { lastSql, lastData }
     ↓
retorna { sql, data, text }
     ↓
Index.vue: placeholder substituído no mesmo índice do array
     ↓
Vue atualiza só aquele item → ChatMessage renderiza texto + SqlBlock + DataTable
```

---

## Por que `fetch` e não o `<Form>` do Inertia?

O componente `<Form>` do Inertia foi projetado para **navegações** — ele manda o request, o servidor responde com um novo estado de página Inertia, e o Vue re-renderiza a página inteira com os novos props.

No chat, o comportamento esperado é diferente:
- A página **não navega** — apenas o array de mensagens muda
- A resposta é **JSON puro** (`{ sql, data, text }`), não um response Inertia
- O estado das mensagens anteriores precisa ser **preservado** entre requests

O `fetch` resolve tudo isso: faz o POST, recebe JSON, e o código atualiza o estado local do Vue sem interferir no ciclo de vida do Inertia.

---

## O Placeholder de Loading

Antes de o request terminar, dois itens são empurrados para `messages` de uma vez:

```ts
messages.value.push(userMessage, placeholder);
```

O `placeholder` tem `loading: true` e um `id` gerado com `crypto.randomUUID()` que é guardado em `placeholderId`. Quando o fetch retorna, o placeholder é localizado pelo id e **substituído no mesmo índice**:

```ts
const index = messages.value.findIndex((m) => m.id === placeholderId);
messages.value[index] = { ...respostaReal };
```

Isso garante que o balão animado vira a resposta no lugar certo, sem remover e re-adicionar itens (o que causaria salto visual).

---

## O CSRF Token

Laravel protege todas as rotas `POST` com o middleware `VerifyCsrfToken`. Para requests feitos via formulários HTML, o token fica num campo hidden `_token`. Para requests `fetch` (JavaScript puro), o token precisa ir no header `X-CSRF-TOKEN`.

Laravel já injeta o token na tag `<meta>` de toda página via `resources/views/app.blade.php`:

```html
<meta name="csrf-token" content="TOKEN_GERADO_PELO_LARAVEL">
```

O código lê esse valor em runtime:

```ts
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content') ?? '';
```

E passa no header do fetch:

```ts
headers: {
    'X-CSRF-TOKEN': csrfToken,
}
```

O middleware do Laravel valida esse header e permite o request. Sem ele, a resposta seria `419 Page Expired`.

---

## Arquivos Envolvidos

| Caminho | Responsabilidade |
|---------|-----------------|
| `resources/js/pages/Analytics/Index.vue` | Estado do chat, lógica do fetch, orquestração |
| `resources/js/components/analytics/ChatInput.vue` | Input do usuário, emite `submit` |
| `resources/js/components/analytics/ChatMessageList.vue` | Lista de mensagens, auto-scroll |
| `resources/js/components/analytics/ChatMessage.vue` | Renderiza um único balão (user/assistant/loading/error) |
| `resources/js/components/analytics/SqlBlock.vue` | Exibe o SQL executado |
| `resources/js/components/analytics/DataTable.vue` | Exibe os dados retornados pela query |
| `app/Http/Controllers/AnalyticsController.php` | Recebe o POST, valida, chama a Action |
| `app/Actions/Analytics/AskAnalyticsAction.php` | Orquestra o agente de IA |
| `app/Ai/Agents/SqlGeneratorAgent.php` | Agente Gemini que gera e executa SQL |
| `app/Ai/Tools/RunQueryTool.php` | Executa o SELECT com segurança, rastreia `lastSql` e `lastData` |
