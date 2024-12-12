<x-app-layout>
    <div class="h-full flex background-app">
        <x-messaging.discussion-sidebar :discussions="$discussions" :friends="$friends" />

        <div id="chat-placeholder" class="flex-1 flex items-center justify-center text-white bg-gray-800">
            <x-messaging.home-section />
        </div>

        <div id="chat-area" class="flex-1 background-app flex flex-col h-full relative hidden">
        
            <x-messaging.header />
            <x-messaging.messages />
            <div id="error-message" style="display: none; color: red; margin-top: 10px;"></div>
            <x-messaging.chatbar />
        </div>
    </div>
</x-app-layout>

<script>
    let currentChatId = null;
    let interval = null;
    let allMessages = [];
    const currentUserId = {{ auth()->id() }};

    function loadChat(chatId, discussionName, discussionPicture, newOpening = true) {
    const messagesContainer = document.getElementById('messages');

    document.getElementById('chat-placeholder').style.display = 'none';
    document.getElementById('chat-area').style.display = 'flex';

    if (currentChatId !== chatId) {
        allMessages = [];
        messagesContainer.innerHTML = '';
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

            const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;

            newMessages.forEach(message => {
                const isCurrentUser = message.user_id === currentUserId;
                const messageElement = document.createElement('div');
                messageElement.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2`;

                let messageContent = `
                    <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                        <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>
                        ${message.message}
                    </p>
                `;

                if (message.media_url) {
                    if (message.media_url.endsWith('.mp4')) {
                        messageContent = `
                            <video controls preload="none" class="w-full" poster="{{ asset('source/assets/images/') }}/video.png">
                                <source src="{{ asset('source/media/') }}/${message.media_url}" type="video/mp4">
                            </video>`;
                    } else if (message.media_url.endsWith('.mp3')) {
                        messageContent = `
                            <audio preload="none" controls class="w-full">
                                <source src="{{ asset('source/media/') }}/${message.media_url}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>`;
                    } else {
                        messageContent = `
                            <img src="{{ asset('source/media/') }}/${message.media_url}" class="w-full rounded-lg">
                            <p class="mt-3">${message.message}</p>`;
                    }
                }

                messageElement.innerHTML = messageContent;
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
            .then(response => {
                return response.json();
            })
            .then(data => {
                const errorMessage = document.getElementById('error-message');
                if (data.error) {
                    errorMessage.textContent = data.error;
                    errorMessage.style.display = 'block'; 
                } else {

                    document.getElementById('message-content').value = '';
                    errorMessage.style.display = 'none'; 
                }
            })
            .catch(error => {
                console.error('Erreur:', error); 
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = 'Une erreur est survenue. Veuillez réessayer.'; 
                errorMessage.style.display = 'block';
            });
    }


    function scrollToBottom() {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    startAutoRefresh();
</script>