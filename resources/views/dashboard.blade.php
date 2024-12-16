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
    function loadChat(chatId, discussionName, discussionPicture, members, newOpening = true) {
    const messagesContainer = document.getElementById('messages');

    // Masquer le placeholder et afficher la zone de chat
    document.getElementById('chat-placeholder').style.display = 'none';
    document.getElementById('chat-area').style.display = 'flex';

    // Réinitialiser les messages si on change de discussion
    if (currentChatId !== chatId) {
        allMessages = [];
        messagesContainer.innerHTML = '';

            // Récupérer le champ contenant l'id pour la création de capsule via son id et mettre à jour l'action
            const hiddenInput = document.getElementById('discussion-id');
            if (hiddenInput) hiddenInput.value = chatId;
        }

        // Mettre à jour l'ID de la discussion actuelle
        currentChatId = chatId;

        // Mettre à jour le titre du header si nécessaire
        if (newOpening) {
            const headerTitle = document.querySelector('.headerTitle');
            if (headerTitle) headerTitle.textContent = discussionName;

            const headerImage = document.querySelector('.headerImage');
            if(headerImage) headerImage.src = discussionPicture;

            if (members && Array.isArray(members)) {
                const membersList = document.getElementById('members-list');
                if (membersList) {
                    membersList.innerHTML = ''; // Vider la liste existante
                    members.forEach(member => {
                        const listItem = document.createElement('li');
                        const memberName = member.name + ({{ auth()->id() }} == member.id ? " (Moi)" : "");
                        const avatarUrl = member.image ? `/storage/${member.image}` : `/source/assets/avatar/avatar.png`;

                        listItem.className = 'flex items-center gap-x-3';
                        listItem.innerHTML = `
                            <img src="${avatarUrl}" alt="Avatar" class="w-8 h-8 rounded-full">
                            <span>${memberName}</span>
                        `;
                        membersList.appendChild(listItem);
                    });
                }
            }
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
                let deletedMessages = [];
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

                // Récupérer la liste des messages supprimés
                deletedMessages = allMessages.filter(message => !data.messages.some(m => m.id === message.id));

                // Récupérer la position dans le scroll et vérifier si on est tout en bas
                // Utile pour savoir si on doit défiler jusqu'en bas après l'ajout des nouveaux messages ou si l'utilisateur est entrain de consulter des anciens messages
                const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 0.6;

                // Parcourir les messages et les ajouter
                newMessages.forEach(message => {
                    const isCurrentUser = message.user_id === {{ auth()->id() }};
                    const messageElement = document.createElement('div');
                    messageElement.id = `message-div-${message.id}`;
                    const prettyDate = new Date(message.created_at).toLocaleString('fr-FR', {
                        day: 'numeric',
                        month: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric'
                    });
                    const prettyOpeningDate = new Date(message.opening_date).toLocaleString('fr-FR', {
                        day: 'numeric',
                        month: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric'
                    });
                    const messageId = message.id;
                    // On check si le <p> avec l'id message-${message.id} à un message.id correspondant à messageId-10000000 existe et modifier son contenu au besoin
                    if (document.getElementById(`message-${messageId-10000000}`)) {
                        document.getElementById(`message-${messageId-10000000}`).innerHTML = `🔓 Ce message a été ouvert le ${prettyOpeningDate}`;
                    }
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

                    // Gestion des médias
                    if (message.media_url) {
                        let mediaElement =
                            `
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
                        }else {
                            mediaElement += `
                    <img src="{{ asset('source/media/') }}/${message.media_url}" class="w-full rounded-lg">`;
                        }

                        mediaElement += `
                        <p class="mt-3" id="message-${message.id}">
                            ${message.message}
                        </p>
                        <!-- Afficher tout à droite la date de created_at -->
                        <span class="text-xs text-white block mb-1 font-bold text-right">${prettyDate}</span>
                    </div>`;
                    messageElement.innerHTML = mediaElement;
                    }
                    messagesContainer.appendChild(messageElement);
                });

                // Parcourir les messages supprimés et enlever la div du message correspondant
                deletedMessages.forEach(message => {
                    const messageElement = document.getElementById(`message-div-${message.id}`);
                    if (messageElement) {
                        messageElement.innerHTML = `<p class="text-gray-500 italic">Ce message a été supprimé.</p>`;
                    }
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

    function startAutoRefresh(intervalTime = 5000) {
        if (interval) clearInterval(interval);

        interval = setInterval(() => {
            if (currentChatId) {
                loadChat(currentChatId, null, null, null, false);
            }
        }, intervalTime);
    }

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
        const messageElement = document.getElementById(`message-div-${messageId}`);
        if (messageElement) {
            messageElement.remove();
        }
        loadChat(discussionId, null, null, null, false);
    })
    .catch(error => {
        console.error("Erreur lors de la suppression :", error);
        alert("Une erreur s'est produite lors de la suppression du message.");
    });
}

    startAutoRefresh();
</script>
