/**
 * WINTECH ERP V2.5 - GIA ASSISTANT ENGINE
 * Logiciel de gestion intelligent - YAOCOM'S GROUPE
 * 
 * Ce script pilote l'interface de l'assistant IA (Chatbot)
 */

const GIA = {
    // --- CONFIGURATION ---
    config: {
        container: 'ai-window',
        chatBox: 'chat-box',
        input: 'ai-input',
        trigger: 'ai-chat-trigger',
        endpoint: '/api/assistant/ask' // Point d'entrée du contrôleur PHP
    },

    // --- INITIALISATION ---
    init: function() {
        this.container = document.getElementById(this.config.container);
        this.chatBox = document.getElementById(this.config.chatBox);
        this.input = document.getElementById(this.config.input);
        
        if (!this.container) return;

        // Charger l'historique de la session si existant
        this.loadHistory();
        this.setupEventListeners();
        
        console.log("🤖 GIA Assistant initialisé.");
    },

    // --- ÉCOUTEURS D'ÉVÉNEMENTS ---
    setupEventListeners: function() {
        // Envoi par clic sur le bouton (défini dans footer.php via onclick="sendAIMessage()")
        // Envoi par la touche Entrée
        this.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
    },

    // --- CORE LOGIC ---
    sendMessage: function() {
        const message = this.input.value.trim();
        if (message === "") return;

        // 1. Afficher le message de l'utilisateur
        this.appendMessage(message, 'user');
        this.input.value = '';

        // 2. Afficher l'indicateur de réflexion (Typing)
        this.showTyping(true);

        // 3. Appel API vers le serveur WinTech
        fetch(this.config.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            this.showTyping(false);
            this.appendMessage(data.reply, 'gia');
            this.saveHistory();
        })
        .catch(error => {
            console.error("Erreur GIA:", error);
            this.showTyping(false);
            this.appendMessage("Désolé, j'ai rencontré une difficulté de connexion avec le serveur central. Veuillez réessayer.", 'gia');
        });
    },

    // --- UI HELPERS ---
    appendMessage: function(text, sender) {
        const div = document.createElement('div');
        
        // Style Elite V3 pour les bulles de texte
        if (sender === 'user') {
            div.className = 'bg-dark text-white p-2 rounded mb-2 align-self-end shadow-sm';
            div.style.cssText = 'max-width: 85%; margin-left: auto; font-size: 0.8rem; border-bottom-right-radius: 2px;';
        } else {
            div.className = 'bg-white border p-2 rounded mb-2 shadow-sm animate-up';
            div.style.cssText = 'max-width: 85%; margin-right: auto; font-size: 0.8rem; border-bottom-left-radius: 2px; border-left: 3px solid #e11d48;';
            div.innerHTML = `<strong>GIA :</strong><br>${text}`;
        }
        
        if(sender === 'user') div.textContent = text; // Sécurité XSS

        this.chatBox.appendChild(div);
        this.scrollToBottom();
    },

    showTyping: function(show) {
        const id = 'gia-typing';
        let typingEl = document.getElementById(id);

        if (show) {
            if (!typingEl) {
                typingEl = document.createElement('div');
                typingEl.id = id;
                typingEl.className = 'text-muted small mb-2 italic';
                typingEl.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> GIA réfléchit...';
                this.chatBox.appendChild(typingEl);
            }
        } else if (typingEl) {
            typingEl.remove();
        }
        this.scrollToBottom();
    },

    scrollToBottom: function() {
        this.chatBox.scrollTop = this.chatBox.scrollHeight;
    },

    // --- PERSISTENCE ---
    saveHistory: function() {
        sessionStorage.setItem('gia_chat_history', this.chatBox.innerHTML);
    },

    loadHistory: function() {
        const history = sessionStorage.getItem('gia_chat_history');
        if (history) {
            this.chatBox.innerHTML = history;
            this.scrollToBottom();
        }
    }
};

// Fonctions globales appelées par le HTML (footer.php)
function toggleAIChat() {
    const win = document.getElementById(GIA.config.container);
    if(win.style.display === 'flex') {
        win.style.display = 'none';
    } else {
        win.style.display = 'flex';
        GIA.scrollToBottom();
    }
}

function sendAIMessage() {
    GIA.sendMessage();
}

// Lancement au démarrage
document.addEventListener('DOMContentLoaded', () => GIA.init());