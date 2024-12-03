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
    let allMessages = [];

    // Fonction pour charger la discussion
    function loadChat(chatId, discussionName, discussionPicture, newOpening = true) {
        const messagesContainer = document.getElementById('messages');

        // Masquer le placeholder et afficher la zone de chat
        document.getElementById('chat-placeholder').style.display = 'none';
        document.getElementById('chat-area').style.display = 'flex';

        // Vérifier si la discussion est déjà chargée
        if (currentChatId !== chatId) {
            // Réinitialiser la liste de tout les messages
            allMessages = [];
            document.getElementById('messages').innerHTML = '';
        }

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
                let newMessages = [];
                // Si la liste de tout les messages correspond à la liste actuelle
                if (JSON.stringify(allMessages) === JSON.stringify(data.messages)) {
                    return;
                } else if (allMessages.length === 0) {
                    // Si la liste de tout les messages est vide
                    newMessages = data.messages;
                } else {
                    // Récupérer la liste des messages qui ne sont pas déjà affichés
                    newMessages = data.messages.filter(message => !allMessages.some(m => m.id === message.id));
                }

                // Récupérer la position dans le scroll et vérifier si on est tout en bas
                // Utile pour savoir si on doit défiler jusqu'en bas après l'ajout des nouveaux messages ou si l'utilisateur est entrain de consulter des anciens messages
                const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;

                // Parcourir les messages et les ajouter
                newMessages.forEach(message => {
                    const isCurrentUser = message.user_id === {{ auth()->id() }};
                    const messageElement = document.createElement('div');
                    messageElement.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2`;
                    messageElement.innerHTML = `
                <!-- Encadré bleu avec le user_id en première ligne -->
                <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                    <!-- Affichage du user_id dans l'encadré bleu -->
                    <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>
                    ${message.message}
                </p>`;

                    // Gestion des médias
                    if (message.media_url) {
                        let mediaElement =
                            `
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
                    </div>`;
                    messageElement.innerHTML = mediaElement;
                    }
                    messagesContainer.appendChild(messageElement);
                });

                // Mettre à jour la liste de tout les messages
                allMessages = data.messages;

                // Attendre que tout les médias soient chargés avant de faire défiler
                const mediaElements = document.querySelectorAll('img, video');
                mediaElements.forEach(mediaElement => {
                    mediaElement.addEventListener('load', () => {
                        if (isAtBottom) {
                            scrollToBottom()
                        };
                    });
                });

                // Faire défiler jusqu'en bas (utile si il y a pas de médias)
                if (isAtBottom) {
                    scrollToBottom()
                };
            })
            .catch(error => console.error('Erreur:', error))
    }

    // Fonction pour démarrer la mise à jour automatique des messages
    function startAutoRefresh(intervalTime = 500) {
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