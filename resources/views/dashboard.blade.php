<x-app-layout>
    <div class="h-full flex background-app">
        <!-- Inclure le composant de la barre de discussions -->
        <x-messaging.discussion-sidebar :discussions="$discussions" :friends="$friends" />

        <!-- Zone de chat avec message par défaut -->
        <div id="chat-placeholder" class="flex-1 flex items-center justify-center text-white bg-gray-800">
            <x-messaging.home-section />
        </div>

        <!-- Section de chat (initialement cachée) -->
        <div id="chat-area" class="flex-1 background-app flex flex-col h-full relative hidden">
            <x-messaging.header/>
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

    // Réinitialiser les messages si on change de discussion
    if (currentChatId !== chatId) {
        allMessages = [];
        messagesContainer.innerHTML = '';

        const hiddenInput = document.getElementById('discussion-id');
        if (hiddenInput) hiddenInput.value = chatId;
    }

    currentChatId = chatId;

    if (newOpening) {
        const headerTitle = document.querySelector('.headerTitle');
        if (headerTitle) headerTitle.textContent = discussionName;

        const headerImage = document.querySelector('.headerImage');
        if (headerImage) headerImage.src = discussionPicture;
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
            if (JSON.stringify(allMessages) === JSON.stringify(data.messages)) {
                return;
            } else if (allMessages.length === 0) {
                newMessages = data.messages;
            } else {
                newMessages = data.messages.filter(message => !allMessages.some(m => m.id === message.id));
            }

            const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 0.6;

            newMessages.forEach(message => {
                const isCurrentUser = message.user_id === {{ auth()->id() }};
                const messageElement = document.createElement('div');
                messageElement.id = `message-${message.id}`;
                messageElement.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2`;

                // Construction du contenu du message
                let messageContent = `
                    <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                        <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>
                        ${message.message}
                    </p>`;

                // Ajout de la poubelle pour les messages de l'utilisateur
                if (isCurrentUser) {
                    messageContent += `
                        <button onclick="deleteMessage(${message.id}, ${chatId})" 
                            class="ml-2 text-red-500 hover:text-red-700 focus:outline-none" 
                            title="Supprimer le message">
                            🗑️
                        </button>`;
                }

                // Ajouter le contenu au conteneur du message
                messageElement.innerHTML = messageContent;

                // Gestion des médias (si présents)
                if (message.media_url) {
                    let mediaElement = `
                        <div class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                            <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>`;

                    if (message.media_url.endsWith('.mp4') || message.media_url.endsWith('.mov')) {
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

            allMessages = data.messages;

            const mediaElements = document.querySelectorAll('img, video');
            mediaElements.forEach(mediaElement => {
                mediaElement.addEventListener('load', () => {
                    if (isAtBottom) {
                        scrollToBottom();
                    }
                });
            });

            if (isAtBottom) {
                scrollToBottom();
            }
        })
        .catch(error => console.error('Erreur:', error));
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

    function scrollToBottom() {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

function leaveChat() {
    if (!currentChatId) {
        alert("Aucune discussion sélectionnée.");
        return;
    }

    fetch(`/chat/${currentChatId}/leave`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error("Impossible de quitter la conversation.");
            }
        })
        .then(data => {
            alert(data.message || "Vous avez quitté la conversation.");
            currentChatId = null;

            document.getElementById('chat-area').style.display = 'none';
            document.getElementById('chat-placeholder').style.display = 'flex';

            location.reload();
        })
        .catch(error => {
            console.error("Erreur :", error);
            alert("Une erreur s'est produite en essayant de quitter la conversation.");
        });
}
function deleteMessage(messageId, discussionId) {
    if (!messageId || !discussionId) {
        alert("Informations de message ou discussion manquantes.");
        return;
    }

    fetch(`/chat/${discussionId}/${messageId}/delete`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            console.error("Erreur de suppression, statut:", response.status);
            throw new Error("Impossible de supprimer le message.");
        }
        return response.json();
    })
    .then(data => {
        alert(data.message || "Message supprimé.");
        const messageElement = document.getElementById(`message-${messageId}`);
        if (messageElement) {
            messageElement.remove();
        }
        loadChat(discussionId, null, null, false);
    })
    .catch(error => {
        console.error("Erreur lors de la suppression :", error);
        alert("Une erreur s'est produite lors de la suppression du message.");
    });
}


    startAutoRefresh(); 
</script>