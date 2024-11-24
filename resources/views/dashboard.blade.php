<x-app-layout>
    <div class="h-full flex background-app">
        <!-- Inclure le composant de la barre de discussions -->
        <x-messaging.discussion-sidebar :discussions="$discussions" :friends="$friends"/>

        <!-- Zone de chat avec message par défaut -->
        <div id="chat-placeholder" class="flex-1 flex items-center justify-center text-white bg-gray-800">
            <x-messaging.home-section />
        </div>

        <!-- Section de chat (initialement cachée) -->
        <div id="chat-area" class="flex-1 background-app flex flex-col h-full relative hidden">
            <x-messaging.header />
            <x-messaging.messages />
            <x-messaging.chatbar />
        </div>
    </div>
</x-app-layout>

<script>
    let currentChatId = null;
    let interval = null;
    let lastMessageId = null;
    let isLoading = false; // Flag pour éviter les chargements simultanés

    // Fonction pour charger la discussion
    function loadChat(chatId, discussionName, discussionPicture, newOpening = true) {
        if (isLoading) return; // Empêcher les requêtes simultanées
        isLoading = true;

        // Masquer le placeholder et afficher la zone de chat
        document.getElementById('chat-placeholder').style.display = 'none';
        document.getElementById('chat-area').style.display = 'flex';

        // Mettre à jour l'ID de la discussion actuelle
        currentChatId = chatId;

        // Mettre à jour le titre du header si nécessaire
        if (newOpening) {
            const headerTitle = document.querySelector('.headerTitle');
            if (headerTitle) headerTitle.textContent = discussionName;

            const headerImage = document.querySelector('.headerImage');
            if(headerImage) headerImage.src = discussionPicture;
        }

        fetch(`/chat/${chatId}/messages`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                const messagesContainer = document.getElementById('messages');
                const newLastMessageId = data.messages.length > 0 ? data.messages[data.messages.length - 1].id :
                    null;

                if (newLastMessageId === lastMessageId) {
                    isLoading = false; // Aucun nouveau message
                    return;
                }

                lastMessageId = newLastMessageId; // Mettre à jour l'ID du dernier message
                messagesContainer.innerHTML = ''; // Vider le conteneur

                // Parcourir les messages et les ajouter
                data.messages.forEach(message => {
                    const isCurrentUser = message.user_id === {{ auth()->id() }};
                    let messageElement = `
            <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2">
                <!-- Encadré bleu avec le user_id en première ligne -->
                <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                    <!-- Affichage du user_id dans l'encadré bleu -->
                    <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>
                    ${message.message}
                </p>
            </div>`;

                    // Gestion des médias
                    if (message.media_url && message.opening_date < new Date().toISOString()) {
                        let mediaElement =
                            `
            <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2">
                <div class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                    <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>`;
                        if (message.media_url.endsWith('.mp4')) {
                            mediaElement += `
                    <video controls preload="none" class="w-full" poster="{{ asset('source/assets/images/') }}/video.png">
                        <source src="{{ asset('source/media/') }}/${message.media_url}" type="video/mp4">
                    </video>`;
                        } else if (message.media_url.endsWith('.mp3')) {
                            mediaElement += `
                    <audio preload="none" controls class="w-full">
                        <source src="{{ asset('source/media/') }}/${message.media_url}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>`;
                        } else {
                            mediaElement += `
                    <img src="{{ asset('source/media/') }}/${message.media_url}" class="w-full rounded-lg">`;
                        }

                        mediaElement += `
                        <p class="mt-3">
                            ${message.message}
                        </p>
                    </div>
                </div>`;
                        messagesContainer.innerHTML += mediaElement;
                    } else {
                        messagesContainer.innerHTML += messageElement;
                    }
                });

                scrollToBottom(); // Faire défiler jusqu'en bas
            })
            .catch(error => console.error('Erreur:', error))
            .finally(() => {
                isLoading = false; // Libérer le flag après chargement
            });
    }

    // Fonction pour démarrer la mise à jour automatique des messages
    function startAutoRefresh(intervalTime = 3000) {
        if (interval) clearInterval(interval);
        interval = setInterval(() => {
            if (currentChatId) loadChat(currentChatId, null, null, false);
        }, intervalTime);
    }

    // Fonction pour envoyer un message
    function sendMessage() {
        const messageContent = document.getElementById('message-content').value;
        if (!messageContent) return;

        fetch(`/chat/${currentChatId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: messageContent
                })
            })
            .then(() => {
                document.getElementById('message-content').value = '';
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Fonction pour faire défiler les messages jusqu'en bas
    function scrollToBottom() {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    startAutoRefresh(); // Démarrer l'auto-rafraîchissement
</script>
