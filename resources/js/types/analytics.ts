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
};
