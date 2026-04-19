<?php
/**
 * =============================================================================
 * WINTECH ERP V2.5 - MASTER FOOTER "SOVEREIGN ELITE"
 * =============================================================================
 * @package     WinTech GIA Edition
 * @author      Senior Native PHP Architect
 * @version     10.0.1 (Production Ready)
 * 
 * DESCRIPTION :
 * Gère l'interactivité globale (JS), les fenêtres flottantes (IA Assistant),
 * la persistence de l'interface (Sidebar) et le monitoring temps réel.
 * =============================================================================
 */
?>
        </main> <!-- Fin du flux de contenu dynamique -->
</div> <!-- Fin du .wrapper global -->

<!-- ---------------------------------------------------------------------------
     1. COMPOSANTS FLOTTANTS (UI OVERLAYS)
---------------------------------------------------------------------------- -->

<!-- BOUTON DÉCLENCHEUR GIA (Floating Action Button) -->
<div id="gia-assistant-trigger" class="gia-fab shadow-pro animate__animated animate__fadeInUp" onclick="GIA_ENGINE.toggleChat()">
    <div class="gia-icon-inner">
        <span class="gia-letter">G</span>
    </div>
    <div class="gia-status-pulse"></div>
</div>

<!-- FENÊTRE DE DIALOGUE INTELLIGENTE (GIA Assistant) -->
<div id="gia-chat-window" class="gia-window shadow-pro">
    <div class="gia-header">
        <div class="d-flex align-items-center">
            <div class="gia-mini-logo">G</div>
            <div class="ms-2">
                <div class="fw-bold small">GIA Assistant</div>
                <div class="extra-small opacity-75">Connecté à YCS-Mainframe</div>
            </div>
        </div>
        <div class="gia-controls">
            <i class="fas fa-minus me-2 cursor-pointer" onclick="GIA_ENGINE.toggleChat()"></i>
            <i class="fas fa-times cursor-pointer" onclick="GIA_ENGINE.clearChat()"></i>
        </div>
    </div>
    
    <div class="gia-chat-body" id="gia-messages-container">
        <!-- Message de bienvenue auto-généré -->
        <div class="msg-gia animate__animated animate__fadeInLeft">
            Bonjour <strong><?= explode(' ', $_SESSION['user_nom'])[0] ?></strong> ! Je suis GIA. 
            Comment puis-je vous aider dans vos opérations <?= strtolower($_SESSION['user_role']) ?> aujourd'hui ?
        </div>
    </div>
    
    <div class="gia-footer">
        <div class="input-group">
            <input type="text" id="gia-input" class="form-control" placeholder="Posez une question à GIA...">
            <button class="btn btn-danger" onclick="GIA_ENGINE.send()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<!-- ---------------------------------------------------------------------------
     2. MOTEUR JAVASCRIPT "SOVEREIGN" (NATIVE JS)
---------------------------------------------------------------------------- -->

<!-- Scripts de base -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * WINTECH INTERFACE ENGINE
 * Module de gestion de l'expérience utilisateur
 */
const WIN_UI = {
    
    // --- INITIALISATION ---
    init: function() {
        this.sidebarLogic();
        this.clockLogic();
        this.tickerLogic();
        this.setupTooltips();
        console.log("🚀 WinTech Engine V2.5 Initialisé.");
    },

    // --- 1. RABATTEUR DE SIDEBAR (PERSISTENT) ---
    sidebarLogic: function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const header  = document.querySelector('.fixed-header-group');
        const btn     = document.getElementById('sidebarCollapse');

        if (!btn) return;

        const toggleAction = () => {
            // Mobile / tablette (wintech.css) : la sidebar est masquée via translateX ;
            // on utilise la classe .active, pas .collapsed.
            if (window.matchMedia('(max-width: 992px)').matches) {
                sidebar.classList.toggle('active');
                return;
            }
            sidebar.classList.toggle('collapsed');
            if (content) content.classList.toggle('expanded');
            if (header) header.classList.toggle('expanded');
            
            // Sauvegarde de l'état pour le confort de l'utilisateur (Module 8)
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('wintech_sidebar_pref', isCollapsed ? 'mini' : 'full');
        };

        btn.addEventListener('click', toggleAction);

        // Restauration intelligente au chargement (desktop uniquement)
        if (!window.matchMedia('(max-width: 992px)').matches
            && localStorage.getItem('wintech_sidebar_pref') === 'mini') {
            sidebar.classList.add('collapsed');
            if (content) content.classList.add('expanded');
            if (header) header.classList.add('expanded');
        }
    },

    // --- 2. HORLOGE ATOMIQUE JAUNE (AM/PM) ---
    clockLogic: function() {
        const update = () => {
            const now = new Date();
            const options = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            };
            const timeString = now.toLocaleTimeString('en-US', options);
            const el = document.getElementById('nav-clock');
            if (el) el.innerText = timeString;
        };
        setInterval(update, 1000);
        update();
    },

    // --- 3. TICKER D'HISTORIQUE (FLUX DYNAMIQUE) ---
    tickerLogic: function() {
        const ticker = document.getElementById('ticker-content');
        if (!ticker) return;

        const events = [
            "Session sécurisée : <?= $_SESSION['user_matricule'] ?> @ <?= $_SERVER['REMOTE_ADDR'] ?>",
            "Moteur de calcul TVA 18% actif",
            "Archivage électronique SAE : Opérationnel",
            "Base de données agence : Connectée",
            "Bienvenue chez YAOCOM'S GROUPE"
        ];
        
        let i = 0;
        setInterval(() => {
            ticker.classList.add('animate__fadeOut');
            setTimeout(() => {
                ticker.innerText = events[i];
                ticker.classList.remove('animate__fadeOut');
                ticker.classList.add('animate__fadeIn');
                i = (i + 1) % events.length;
            }, 500);
        }, 6000);
    },

    // --- 4. TOOLTIPS ---
    setupTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
};

/**
 * GIA ASSISTANT ENGINE (MODULE 7)
 */
const GIA_ENGINE = {
    toggleChat: function() {
        const win = document.getElementById('gia-chat-window');
        win.style.display = (win.style.display === 'flex') ? 'none' : 'flex';
        document.getElementById('gia-input').focus();
    },

    send: function() {
        const input = document.getElementById('gia-input');
        const container = document.getElementById('gia-messages-container');
        const text = input.value.trim();

        if (text === "") return;

        // Message Utilisateur
        const uMsg = document.createElement('div');
        uMsg.className = 'msg-user animate__animated animate__fadeInRight';
        uMsg.innerText = text;
        container.appendChild(uMsg);
        input.value = '';

        // Simulation Intelligence GIA
        setTimeout(() => {
            const gMsg = document.createElement('div');
            gMsg.className = 'msg-gia animate__animated animate__fadeIn';
            gMsg.innerHTML = "J'analyse votre demande... <br><small>Fonctionnalité en cours de liaison avec le module analytique.</small>";
            container.appendChild(gMsg);
            container.scrollTop = container.scrollHeight;
        }, 1000);

        container.scrollTop = container.scrollHeight;
    },

    clearChat: function() {
        if(confirm("Effacer la conversation avec GIA ?")) {
            document.getElementById('gia-messages-container').innerHTML = '';
            this.toggleChat();
        }
    }
};

// LANCEMENT DU MOTEUR
document.addEventListener('DOMContentLoaded', () => WIN_UI.init());
</script>

<!-- ---------------------------------------------------------------------------
     3. STYLE SCOPÉ (UI COMPONENTS)
---------------------------------------------------------------------------- -->
<style>
/* Finesse de l'assistant IA GIA */
.gia-fab { 
    position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; 
    background: var(--primary-red); border-radius: 50%; cursor: pointer; 
    display: flex; align-items: center; justify-content: center; z-index: 2000;
    border: 4px solid white; transition: var(--transition);
}
.gia-fab:hover { transform: scale(1.1) rotate(15deg); }
.gia-letter { color: white; font-weight: 800; font-size: 1.8rem; font-family: 'Poppins', sans-serif; font-style: italic; }
.gia-status-pulse { 
    position: absolute; top: 0; right: 0; width: 15px; height: 15px; 
    background: var(--success-green); border-radius: 50%; border: 2px solid white; 
}

.gia-window { 
    position: fixed; bottom: 105px; right: 30px; width: 350px; height: 500px; 
    background: white; border-radius: 20px; z-index: 2001; display: none; 
    flex-direction: column; overflow: hidden; border: 1px solid var(--border-color);
}
.gia-header { background: var(--primary-red); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
.gia-chat-body { flex-grow: 1; padding: 20px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 10px; }

.msg-gia { background: white; padding: 12px; border-radius: 15px 15px 15px 2px; font-size: 0.82rem; box-shadow: var(--shadow-sm); border-left: 3px solid var(--primary-red); max-width: 85%; }
.msg-user { background: var(--black-matte); color: white; padding: 12px; border-radius: 15px 15px 2px 15px; font-size: 0.82rem; align-self: flex-end; max-width: 85%; }

.gia-footer { padding: 15px; background: white; border-top: 1px solid #eee; }
.gia-footer input { border-radius: 10px; font-size: 0.85rem; border: 1px solid #eee; background: #f1f5f9; }

.extra-small { font-size: 0.65rem; }
.cursor-pointer { cursor: pointer; }
</style>

</body>
</html>