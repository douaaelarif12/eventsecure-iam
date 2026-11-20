    <!-- ===============================
         FOOTER
         ⚠️ NE DOIT PAS ÊTRE FIXED
    =============================== -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-1">
                &copy; <?php echo date('Y'); ?> Système de Gestion des Événements – ENSA El Jadida
            </p>
            <small>
                Projet réalisé par elarif douaa
            </small>
        </div>
    </footer>

    <!-- ===============================
         CHATBOT GLOBAL (HORS FOOTER)
         ✔ Toujours visible
         ✔ N'affecte pas le layout
    =============================== -->

    <!-- Bouton flottant -->
    <div id="chatbot-button" title="Besoin d'aide ?">
        💬
    </div>

    <!-- Widget chatbot -->
    <div id="chatbot-widget" aria-hidden="true">
        <div class="chatbot-header">
            <span>🤖 Assistant Événements</span>
            <button id="chatbot-close" aria-label="Fermer">✕</button>
        </div>

        <div class="chatbot-messages" id="chatbot-messages">
            <div class="bot-message">
                Bonjour 👋<br>
                Posez-moi une question sur :
                <ul class="mb-0">
                    <li>📅 Événements</li>
                    <li>📝 Inscriptions</li>
                    <li>⏰ Dates importantes</li>
                </ul>
            </div>
        </div>

        <div class="chatbot-input">
            <input
                type="text"
                id="chatbot-input"
                placeholder="Écrivez votre question..."
                autocomplete="off"
            >
            <button id="chatbot-send">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <!-- ===============================
         SCRIPTS GLOBAUX
         ⚠️ ORDRE IMPORTANT
    =============================== -->

    <!-- Bootstrap (obligatoirement avant les JS maison) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chatbot -->
    <link rel="stylesheet" href="/gestion_evenements/assets/chatbot.css">
    <script src="/gestion_evenements/assets/chatbot.js?v=2" defer></script>

    <!-- Notifications -->
    <script src="/gestion_evenements/assets/js/notifications.js" defer></script>

    <!-- Script global -->
    <script src="/gestion_evenements/assets/js/script.js" defer></script>
    <script src="/gestion_evenements/assets/js/notifications.js"></script>

</body>
</html>
