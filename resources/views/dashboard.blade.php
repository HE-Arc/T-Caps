<x-app-layout>
    <div class="h-full flex background-app">
        <!-- Inclure le composant de la barre de discussions -->
        <x-messaging.discussion-sidebar :discussions="$discussions" />
        <!-- Inclure le composant de la zone de chat -->
        <x-messaging.chat-area />
    </div>
</x-app-layout>

<script>
    // Store the current discussion ID
    let currentChatId = null;
    // Store the interval reference
    let interval = null;
    // Storing the last message ID
    let lastMessageId = null;

    function loadChat(chatId) {
        // Update the current discussion ID
        currentChatId = chatId;

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
            const newLastMessageId = data.messages.length > 0 ? data.messages[data.messages.length - 1].id : null;

            if (newLastMessageId !== lastMessageId) {
                // Update the last message ID
                lastMessageId = newLastMessageId;
                // Clear the container
                messagesContainer.innerHTML = '';

                data.messages.forEach(message => {
                    const isCurrentUser = message.user_id === {{ auth()->id() }};
                    const messageElement = `
                        <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2">
                            <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                                ${message.message}
                            </p>
                        </div>`;
                    if (!message.media_url && message.opening_date < new Date().toISOString()){
                        messagesContainer.innerHTML += messageElement;
                    } else if (message.opening_date < new Date().toISOString()) {
                        let mediaElement = `
                            <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2">
                                <div class="max-w-[45%] ${isCurrentUser ? 'rounded-tl-lg' : 'rounded-tr-lg'} rounded-bl-lg rounded-br-lg">`;

                        if (message.media_url.endsWith('.mp4')) {
                            mediaElement += `
                                <video controls preload="none" class="w-full ${isCurrentUser ? 'rounded-tl-lg' : 'rounded-tr-lg'}" id="video-${message.id}" poster="{{ asset('source/assets/images/') }}/video.png">
                                    <source src="{{ asset('source/media/') }}/${message.media_url}" type="video/mp4">
                                </video>`;
                        } else if (message.media_url.endsWith('.mp3')) {
                            mediaElement += `
                                <audio preload="none" controls class="w-full ${isCurrentUser ? 'rounded-tl-lg' : 'rounded-tr-lg'}">
                                    <source src="{{ asset('source/media/') }}/${message.media_url}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>`;
                        } else {
                            mediaElement += `
                                <img src="{{ asset('source/media/') }}/${message.media_url}" class="w-full ${isCurrentUser ? 'rounded-tl-lg' : 'rounded-tr-lg'}">`;
                        }

                        mediaElement += `
                                    <p class="secondary-background-app text-white p-2 rounded-bl-lg rounded-br-lg">
                                        ${message.message}
                                    </p>
                                </div>
                            </div>`;
                        messagesContainer.innerHTML += mediaElement;
                    }
                });
                // Wait for the all the medias (images and video) to be loaded before scrolling to the bottom
                const mediaElements = document.querySelectorAll('img, video');
                mediaElements.forEach(mediaElement => {
                    mediaElement.addEventListener('load', () => {
                        scrollToBottom();
                    });
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function startAutoRefresh(intervalTime = 500) {
        // Remove the previous interval if it exists
        if (interval) clearInterval(interval);

        // Start a new interval
        interval = setInterval(() => {
            if (currentChatId) {
                // Load the chat for the current discussion
                loadChat(currentChatId);
            }
        }, intervalTime);
    }

    // Start the auto refresh
    startAutoRefresh();

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
        .then(response => response.json())
        .then(data => {
            // Clear the message input
            document.getElementById('message-content').value = '';
        })
        .catch(error => console.error('Error:', error));
    }

    function scrollToBottom() {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
</script>
