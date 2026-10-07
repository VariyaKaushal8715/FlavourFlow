@props(['site'])

<div id="flavourflow-chatbot" class="fixed bottom-5 right-5 z-50 flex flex-col items-end">
    {{-- Chatbot Toggle Button --}}
    <button
        type="button"
        id="chatbot-toggle-btn"
        class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-red-700 to-amber-500 text-white shadow-2xl transition duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
        aria-label="Open AI Assistant"
    >
        <svg id="chatbot-icon-open" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg id="chatbot-icon-close" class="hidden h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chatbot Floating Window --}}
    <div
        id="chatbot-window"
        class="hidden mt-3 w-[min(92vw,22rem)] sm:w-96 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition-all duration-300"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-950 px-4 py-3.5 text-white">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-400/20 text-amber-400 ring-1 ring-amber-400/30">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">FlavourFlow AI Assistant</h3>
                    <p class="text-[0.65rem] text-amber-300">Spices & Orders Assistant</p>
                </div>
            </div>
            <button type="button" id="chatbot-close-btn" class="rounded-lg p-1 text-zinc-400 transition hover:bg-white/10 hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Messages Container --}}
        <div id="chatbot-messages" class="flex flex-col gap-3 p-4 h-80 overflow-y-auto bg-zinc-50/50 text-xs">
            <div class="self-start max-w-[85%] rounded-2xl rounded-tl-none bg-white p-3 text-zinc-800 shadow-sm border border-zinc-100">
                Hello! 👋 I'm your FlavourFlow AI assistant. Ask me anything about our fresh spices, product recommendations, prices, or your order status!
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div id="chatbot-loading" class="hidden px-4 py-2 text-xs text-zinc-400 flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>AI is searching store information...</span>
        </div>

        {{-- Input Form --}}
        <form id="chatbot-form" class="flex items-center gap-2 border-t border-zinc-100 bg-white p-3">
            <input
                type="text"
                id="chatbot-input"
                required
                placeholder="Ask about spices or orders..."
                class="flex-1 rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-brand-primary focus:bg-white focus:ring-1 focus:ring-brand-primary"
            />
            <button
                type="submit"
                id="chatbot-send-btn"
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-950 text-white transition hover:bg-brand-primary disabled:opacity-50"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>
</div>

