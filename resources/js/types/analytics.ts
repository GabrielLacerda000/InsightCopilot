export type Message = {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    sql?: string;
    data?: Record<string, unknown>[];
    loading?: boolean;
    error?: boolean;
};

export type AskResponse = {
    sql: string;
    data: Record<string, unknown>[];
    text: string;
    conversation_id: string;
};

export type Conversation = {
    id: string;
    title: string;
    updated_at: string;
};
