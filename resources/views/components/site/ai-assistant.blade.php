<div id="ai-assistant-widget" class="relative z-50" data-chat-endpoint="{{ route('ai.chat') }}" data-history-endpoint="{{ route('ai.chat.history') }}" data-clear-endpoint="{{ route('ai.chat.clear') }}">
    <!-- Floating Trigger Button (Bottom-Right) -->
    <button
        id="ai-assistant-trigger"
        type="button"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 rounded-full bg-gradient-to-r from-amber-600 to-red-700 px-4 py-3 text-white shadow-xl hover:from-amber-700 hover:to-red-800 focus:outline-none focus:ring-4 focus:ring-amber-500/30 transition-all duration-300 transform hover:scale-105"
        aria-label="Open FlavourFlow AI Assistant"
    >
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-300 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-200"></span>
        </span>
        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
        </svg>
        <span class="text-xs font-bold tracking-wide uppercase">AI Assistant</span>
    </button>

    <!-- Chat Window Container (Slide-over Drawer / Floating Box) -->
    <div
        id="ai-assistant-modal"
        class="fixed inset-x-0 bottom-0 z-50 hidden h-[85vh] w-full flex-col rounded-t-3xl border border-zinc-200 bg-white shadow-2xl transition-all duration-300 sm:inset-auto sm:right-6 sm:bottom-20 sm:h-[580px] sm:w-[420px] sm:rounded-2xl"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-100 bg-gradient-to-r from-amber-900 to-zinc-950 p-4 text-white rounded-t-3xl sm:rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-600/30 border border-amber-400/40 text-amber-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">FlavourFlow AI Assistant</h3>
                    <p class="text-[10px] text-amber-200/80 font-mono">Multilingual Shopping Assistant</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Status Badge -->
                <div id="ai-provider-status" class="hidden items-center gap-1 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border border-zinc-700 bg-zinc-800 text-zinc-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                    <span id="ai-provider-status-text">Checking</span>
                </div>
                <button
                    id="ai-assistant-clear"
                    type="button"
                    title="Clear Conversation"
                    class="rounded-lg p-1.5 text-zinc-300 hover:bg-white/10 hover:text-white transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
                <button
                    id="ai-assistant-close"
                    type="button"
                    class="rounded-lg p-1.5 text-zinc-300 hover:bg-white/10 hover:text-white transition"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Quick Multilingual Prompt Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto border-b border-zinc-100 bg-zinc-50 p-2.5 text-[11px]">
            <span class="text-[10px] font-bold uppercase text-zinc-400 whitespace-nowrap pl-1">Ask:</span>
            <button type="button" class="ai-quick-prompt rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-zinc-700 hover:border-amber-500 hover:text-amber-700 whitespace-nowrap transition">
                🇬🇧 Recommend best spices
            </button>
            <button type="button" class="ai-quick-prompt rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-zinc-700 hover:border-amber-500 hover:text-amber-700 whitespace-nowrap transition">
                🇮🇳 bhai accha haldi powder dikhao
            </button>
            <button type="button" class="ai-quick-prompt rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-zinc-700 hover:border-amber-500 hover:text-amber-700 whitespace-nowrap transition">
                🚩 મારે લાલ મરચું જોઈએ છે
            </button>
        </div>

        <!-- Chat Messages Container -->
        <div id="ai-chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
            <!-- Initial Assistant Greeting -->
            <div class="flex items-start gap-2.5">
                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">
                    AI
                </div>
                <div class="rounded-2xl rounded-tl-none bg-zinc-100 p-3 text-zinc-800 leading-relaxed max-w-[85%]">
                    Hello! I am your FlavourFlow Shopping Assistant. Ask me about products, prices, tracking orders, or spice recommendations in <b>English, Hindi, Hinglish, Gujarati, or GujEnglish</b>!
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="ai-typing-indicator" class="hidden px-4 py-2">
            <div class="flex items-center gap-2 text-[11px] text-zinc-400 italic">
                <div class="flex gap-1">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-amber-600"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-amber-600 [animation-delay:0.2s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-amber-600 [animation-delay:0.4s]"></span>
                </div>
                <span>FlavourFlow AI is searching & reasoning...</span>
            </div>
        </div>

        <!-- Input Form Footer -->
        <form id="ai-chat-form" class="border-t border-zinc-100 bg-white p-3">
            <div class="flex items-center gap-2">
                <input
                    id="ai-chat-input"
                    type="text"
                    placeholder="Type your question (English, Hindi, Gujarati)..."
                    class="flex-1 rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 focus:border-amber-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                    required
                />
                <button
                    type="submit"
                    id="ai-chat-submit"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-600 text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-600/30 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3 21l18-9L3 3l3 9zm0 0h75" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggerBtn = document.getElementById('ai-assistant-trigger');
    const modal = document.getElementById('ai-assistant-modal');
    const closeBtn = document.getElementById('ai-assistant-close');
    const clearBtn = document.getElementById('ai-assistant-clear');
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const messagesContainer = document.getElementById('ai-chat-messages');
    const typingIndicator = document.getElementById('ai-typing-indicator');

    const widget = document.getElementById('ai-assistant-widget');
    const chatEndpoint = widget.dataset.chatEndpoint;
    const historyEndpoint = widget.dataset.historyEndpoint;
    const clearEndpoint = widget.dataset.clearEndpoint;

    // Toggle Modal
    triggerBtn.addEventListener('click', function () {
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
        if (!modal.classList.contains('hidden')) {
            chatInput.focus();
            loadHistory();
        }
    });

    closeBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    // Clear History
    clearBtn.addEventListener('click', function () {
        if (confirm('Clear chat history?')) {
            fetch(clearEndpoint, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.provider_available !== undefined) {
                    updateProviderStatus(data.provider_available);
                }
                messagesContainer.innerHTML = `
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">AI</div>
                        <div class="rounded-2xl rounded-tl-none bg-zinc-100 p-3 text-zinc-800 leading-relaxed max-w-[85%]">
                            Chat history cleared. How can I help you today?
                        </div>
                    </div>
                `;
            });
        }
    });

    // Quick Prompts
    document.querySelectorAll('.ai-quick-prompt').forEach(button => {
        button.addEventListener('click', function () {
            const promptText = this.innerText.replace(/^[^\s]+\s*/, '');
            chatInput.value = promptText;
            chatForm.dispatchEvent(new Event('submit'));
        });
    });

    // Send Message
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        appendUserMessage(message);
        chatInput.value = '';
        showTyping(true);

        fetch(chatEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            showTyping(false);
            if (data.provider_available !== undefined) {
                updateProviderStatus(data.provider_available);
            }
            if (data.success && data.response) {
                appendAssistantMessage(data.response);
            } else {
                appendErrorMessage();
            }
        })
        .catch(err => {
            showTyping(false);
            appendErrorMessage();
            updateProviderStatus(false);
        });
    });

    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'flex justify-end';
        div.innerHTML = `
            <div class="rounded-2xl rounded-tr-none bg-gradient-to-r from-amber-600 to-red-700 p-3 text-white max-w-[85%] leading-relaxed">
                ${escapeHtml(text)}
            </div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    function appendAssistantMessage(resp) {
        const div = document.createElement('div');
        div.className = 'flex items-start gap-2.5';

        let productsHtml = '';
        if (resp.products && resp.products.length > 0) {
            productsHtml = `<div class="mt-3 grid gap-2">` + resp.products.map(p => `
                <div class="rounded-xl border border-zinc-200 bg-white p-2.5 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <img src="${p.image_url}" alt="${escapeHtml(p.name)}" class="h-10 w-10 rounded-lg object-cover flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-zinc-950 truncate">${escapeHtml(p.name)}</h4>
                            <p class="text-[11px] font-bold text-amber-700">${p.formatted_price}</p>
                        </div>
                    </div>
                    <p class="mt-1.5 text-[10px] text-zinc-500 bg-zinc-50 p-1.5 rounded">${escapeHtml(p.reason)}</p>
                    <div class="mt-2 flex gap-1.5">
                        <a href="${p.url}" class="flex-1 rounded-lg bg-zinc-100 py-1 text-center font-semibold text-[10px] text-zinc-800 hover:bg-zinc-200">View</a>
                        <button onclick="window.location.href='${p.url}'" class="flex-1 rounded-lg bg-amber-600 py-1 text-center font-bold text-[10px] text-white hover:bg-amber-700">Add to Cart</button>
                    </div>
                </div>
            `).join('') + `</div>`;
        }

        let actionsHtml = '';
        if (resp.actions && resp.actions.length > 0) {
            actionsHtml = `<div class="mt-2.5 flex flex-wrap gap-1.5">` + resp.actions.map(a => `
                <a href="${a.url}" class="rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 font-bold text-[10px] text-amber-900 hover:bg-amber-100">${escapeHtml(a.label)}</a>
            `).join('') + `</div>`;
        }

        div.innerHTML = `
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-amber-600 text-white font-bold text-xs">AI</div>
            <div class="rounded-2xl rounded-tl-none bg-zinc-100 p-3 text-zinc-800 leading-relaxed max-w-[88%]">
                <div>${escapeHtml(resp.message).replace(/\n/g, '<br>')}</div>
                ${productsHtml}
                ${actionsHtml}
            </div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    function appendErrorMessage() {
        const div = document.createElement('div');
        div.className = 'flex items-start gap-2.5';
        div.innerHTML = `
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-xs">AI</div>
            <div class="rounded-2xl rounded-tl-none bg-rose-50 border border-rose-100 p-3 text-rose-800 leading-relaxed max-w-[85%]">
                Something went wrong. Please check your connection and try again.
            </div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    function loadHistory() {
        fetch(historyEndpoint)
            .then(res => res.json())
            .then(data => {
                if (data.provider_available !== undefined) {
                    updateProviderStatus(data.provider_available);
                }
                if (data.success && data.history && data.history.length > 0) {
                    messagesContainer.innerHTML = '';
                    data.history.forEach(item => {
                        if (item.sender === 'user') {
                            appendUserMessage(item.message);
                        } else {
                            appendAssistantMessage(item);
                        }
                    });
                }
            })
            .catch(() => {
                updateProviderStatus(false);
            });
    }

    function showTyping(show) {
        if (show) {
            typingIndicator.classList.remove('hidden');
        } else {
            typingIndicator.classList.add('hidden');
        }
        scrollToBottom();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function updateProviderStatus(available) {
        const statusBadge = document.getElementById('ai-provider-status');
        const statusText = document.getElementById('ai-provider-status-text');
        if (!statusBadge || !statusText) return;
        
        statusBadge.classList.remove('hidden', 'flex');
        statusBadge.classList.add('flex');
        
        if (available) {
            statusBadge.className = 'flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border border-emerald-500/30 bg-emerald-500/10 text-emerald-400';
            statusText.innerText = 'AI Active';
        } else {
            statusBadge.className = 'flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border border-rose-500/30 bg-rose-500/10 text-rose-450';
            statusText.innerText = 'Offline Mode';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>
