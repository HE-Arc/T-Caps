<x-app-layout>
    <div class="h-full flex background-app">
        <!-- Include the discussions sidebar component -->
        <x-messaging.discussion-sidebar :discussions="$discussions" :friends="$friends" />

        <!-- Chat area placeholder (initially shown) -->
        <div id="chat-placeholder" class="flex-1 flex items-center justify-center text-white bg-gray-800">
            <x-messaging.home-section />
        </div>

        <!-- Chat section (initially hidden) -->
        <div id="chat-area" class="flex-1 background-app flex flex-col h-full relative hidden">
            <x-messaging.header/>
            <x-messaging.messages />
            <x-messaging.chatbar />
        </div>
    </div>
</x-app-layout>

<script>
    let currentChatId = null;
    // This is the interval in milliseconds for the chat auto-refresh (getting messages)
    let interval = 500;
    let allMessages = [];

    /**
     * Function to load a chat
     * 
     * @param {number} chatId The ID of the chat
     * @param {string} discussionName The name of the chat
     * @param {string} discussionPicture The picture of the chat
     * @param {array} members The members of the chat
     * @param {boolean} newOpening Whether we are opening a new chat or not
     * @returns {void}
     */
    function loadChat(chatId, discussionName, discussionPicture, members, newOpening = true) {
        const messagesContainer = document.getElementById('messages');

        // Hide the placeholder and show the chat area
        document.getElementById('chat-placeholder').style.display = 'none';
        document.getElementById('chat-area').style.display = 'flex';

        // Reset the messages if we change the selected chat
        if (currentChatId !== chatId) {
            allMessages = [];
            messagesContainer.innerHTML = '';

            // Update the hidden input with the chat ID
            const hiddenInput = document.getElementById('discussion-id');
            if (hiddenInput) hiddenInput.value = chatId;
        }

        currentChatId = chatId;

        // Updating the header title and the header image if the user is opening a new chat
        if (newOpening) {
            const headerTitle = document.querySelector('.headerTitle');
            if (headerTitle) headerTitle.textContent = discussionName;

            const headerImage = document.querySelector('.headerImage');
            if(headerImage) headerImage.src = discussionPicture;

            if (members && Array.isArray(members)) {
                const membersList = document.getElementById('members-list');
                if (membersList) {
                    membersList.innerHTML = '';
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

        // Fetch the messages of the chat (chatId)
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
                // If the list of all messages matches the current list
                if (JSON.stringify(allMessages) === JSON.stringify(data.messages)) {
                    // No need to update the messages
                    return;
                } else if (allMessages.length === 0) {
                    // If the list of all messages is empty, the new messages are all the messages
                    newMessages = data.messages;
                } else {
                    // Taking the messages that are not already displayed
                    newMessages = data.messages.filter(message => !allMessages.some(m => m.id === message.id));
                }

                // Get the list of deleted messages
                deletedMessages = allMessages.filter(message => !data.messages.some(m => m.id === message.id));

                // Get the scroll position and check if we are at the bottom
                // Useful to know if we should scroll to the bottom after adding new messages or if the user is viewing old messages
                const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 0.6;

                // Go through the messages and add them to the messages container
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
                    // We check if the <p> with the id message-${message.id} has a message.id corresponding to messageId-10000000 and modify its content if necessary
                    if (document.getElementById(`message-${messageId-10000000}`)) {
                        document.getElementById(`message-${messageId-10000000}`).innerHTML = `🔓 Ce message a été ouvert le ${prettyOpeningDate}`;
                    }
                    messageElement.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'} mb-2`;

                    // Building the message content
                    let messageContent = `
                        <p class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                            <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>
                            ${message.message}
                            <span class="text-xs text-white block mb-1 font-bold text-right">${prettyDate}</span>
                        </p>
                        `;

                    // Adding the trash for the user's messages
                    if (isCurrentUser) {
                        messageContent += `
                            <button onclick="deleteMessageWithLoader(${message.id}, ${chatId})"
                                class="ml-2 text-red-500 hover:text-red-700 focus:outline-none"
                                title="Supprimer le message">
                                🗑️
                            </button>
                            <span id="loader-${message.id}" style="display:none;">
                                 <div class="spinner"></div> 
                            </span>`;
                    }

                    // Adding the message content to the message container
                    messageElement.innerHTML = messageContent;

                    // If there is a media URL, we add a custom message that handles the media
                    if (message.media_url) {
                        let mediaElement =
                            `
                <div class="max-w-[45%] ${isCurrentUser ? 'secondary-background-app rounded-tl-lg' : 'tertiary-background-app rounded-tr-lg'} text-white p-2 rounded-bl-lg rounded-br-lg">
                    <span class="text-xs text-white block mb-1 font-bold">${message.user.name}</span>`;
                        if (message.media_url.endsWith('.mp4') || message.media_url.endsWith('.mov') || message.media_url.endsWith('.MOV')) {
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
                    <span class="text-xs text-white block mb-1 font-bold text-right">${prettyDate}</span>
                </div>`;
                if (isCurrentUser){
                    mediaElement += `   <button onclick="deleteMessageWithLoader(${message.id}, ${chatId})"
                                            class="ml-2 text-red-500 hover:text-red-700 focus:outline-none"
                                            title="Supprimer le message">
                                            🗑️
                                        </button>
                                         <span id="loader-${message.id}"  style="display:none;">
                                            <div class="spinner"></div> 
                                         </span>`;
                }
                    messageElement.innerHTML = mediaElement;
                    }

                    // Adding the message element to the messages container
                    messagesContainer.appendChild(messageElement);
                });

                // Go through the deleted messages and remove the div of the corresponding message
                deletedMessages.forEach(message => {
                    const messageElement = document.getElementById(`message-div-${message.id}`);
                    if (messageElement) {
                        messageElement.innerHTML = `<p class="text-gray-500 italic">Ce message a été supprimé.</p>`;
                    }
                });

                // Updating the list of all messages that have been displayed
                allMessages = data.messages;

                // Wait for all media to be loaded before scrolling
                // Useful for slow connections (and slow servers)
                const mediaElements = document.querySelectorAll('img, video');
                mediaElements.forEach(mediaElement => {
                    mediaElement.addEventListener('load', () => {
                        if (isAtBottom) {
                            scrollToBottom()
                        };
                    });
                });

                // Scroll to the bottom (useful if there are no media)
                if (isAtBottom) {
                    scrollToBottom()
                };
            })
            .catch(error => console.error('Erreur:', error))
    }

    /**
     * Function to start the auto-refresh of the chat (getting messages)
     * 
     * @param {number} interval The interval in milliseconds between each refresh
     * @returns {void}
     */
    function startAutoRefresh(interval) {
        if (interval) clearInterval(interval);

        interval = setInterval(() => {
            if (currentChatId) {
                loadChat(currentChatId, null, null, null, false);
            }
        }, interval);
    }

/**
 * Function to send a message
 * 
 * @returns {Promise<void>}
 */
async function sendMessage() {
    const messageContent = document.getElementById('message-content').value;
    if (!messageContent) return;

    try {
        await fetch(`/chat/${currentChatId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                message: messageContent
            })
        });
        document.getElementById('message-content').value = '';
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Function to send a message with a loader
 * 
 * @returns {void}  
 */
async function sendMessageWithLoader() {
    const sendButton = document.getElementById('send-message-btn');
    const loader = document.getElementById('send-message-loader');
    
    sendButton.style.display = 'none';
    loader.style.display = 'block';

    try {
        await sendMessage();
    } catch (error) {
        console.error('Erreur lors de l\'envoi du message:', error);
    } finally {
        sendButton.style.display = 'block';
        loader.style.display = 'none';
    }
}


    /**
     * Function to scroll to the bottom of the messages container
     * 
     * @returns {void}
     */
    function scrollToBottom() {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    /**
     * Function to leave a chat
     * 
     * @returns {void} or response of the fetch
     */
    function leaveChat() {
        if (!currentChatId) {
            alert("Aucune discussion sélectionnée.");
            return;
        }

        // Send the request to leave the chat
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

    /**
     * Function to delete a message
     * 
     * @param {number} messageId The ID of the message
     * @param {number} discussionId The ID of the discussion
     * @returns {void} or response of the fetch
     */
    async function deleteMessage(messageId, discussionId) {
    if (!messageId || !discussionId) {
        alert("Informations de message ou discussion manquantes.");
        return;
    }
    console.log("messageId", messageId)
    
    if(messageId >= 10000000)
    {
        const response = await fetch(`/chat/${discussionId}/${messageId - 10000000}/delete`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
    }
    else{
        const response = await fetch(`/chat/${discussionId}/${messageId}/delete`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
    }

    if (!response.ok) {
        console.error("Erreur de suppression, statut:", response.status);
        throw new Error("Impossible de supprimer le message.");
    }
 
    const messageElement = document.getElementById(`message-div-${messageId}`);
    if (messageElement) {
        messageElement.remove();
    }
}


function leaveChatWithLoad()
{
    const loader = document.getElementById('leave-chat-loader');
    const button = event.target;
    button.style.display = 'none';
    loader.style.display = 'inline';

    leaveChat()
        .then(() => {
            loader.style.display = 'none';
            button.style.display = 'inline';
        })
        .catch((error) => {
            console.error(error);
            loader.style.display = 'none';
            button.style.display = 'inline';
        });
}

async function deleteMessageWithLoader(messageId, chatId) {
    const loader = document.getElementById(`loader-${messageId}`);
    const button = event.target;

    button.style.display = 'none';
    loader.style.display = 'inline';

    try {
        await deleteMessage(messageId, chatId);
    } catch (error) {
        console.error(error);
    } finally {
        loader.style.display = 'none';
        button.style.display = 'inline';
    }
}   


    /**
     * Function to update the file name in the form
     * 
     * @returns {void}
     */
    function updateFileName() {
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('file-info');

        if (fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            fileInfo.innerHTML = `<p class='text-green-400 font-medium'>Fichier sélectionné : ${fileName}</p>`;
        }
    }

    /**
     * Function to handle the drag over event for the file drop
     * 
     * @param {Event} event The event object
     * @returns {void}
     */
    function handleDragOver(event) {
        event.preventDefault();
    }

    /**
     * Function to handle the drop event for the file drop
     * 
     * @param {Event} event The event object
     * @returns {void}
     */
    function handleDrop(event) {
        event.preventDefault();
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('file-info');

        if (event.dataTransfer.files.length > 0) {
            const file = event.dataTransfer.files[0];
            fileInput.files = event.dataTransfer.files;
            fileInfo.innerHTML = `<p class='text-green-400 font-medium'>Fichier sélectionné : ${file.name}</p>`;
        }
    }

    /**
     * Function to handle the capsule form
     * 
     * @returns {void}
     */
    function capsuleForm() {
        return {
            chatMessage: '',
            file: null,
            submitForm(event) {
                const form = document.getElementById('create-file-modal-form');
                // Check if the form is valid
                if (!form.checkValidity()) {
                    event.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires.');
                    return;
                }

                // Take the discussion ID (chatID) from the hidden input
                const discussionId = document.getElementById('discussion-id').value;

                // Create the object to send from the form data
                const formData = new FormData(form);

                // Send the request to create the capsule
                fetch(`/chat/${discussionId}/capsule`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message.id) {
                        // Close the modal and reset the form
                        this.chatMessage = '';
                        this.file = null;
                        document.getElementById('file-info').innerHTML = `<p class='text-gray-300 font-medium'>Glissez et déposez votre fichier ici ou</p><p class='text-blue-400 underline'>cliquez pour sélectionner un fichier</p>`;
                        document.getElementById('date-time').value = '';
                        this.$dispatch('close');
                    } else {
                        console.log(data)
                        alert('Erreur lors de l\'envoi de la capsule');
                    }
                })
                .catch(error => {
                    alert('Erreur lors de l\'envoi');
                    console.error(error);
                });
            }
        };
    }

    // Load the chat when the page is loaded (auto-refresh)
    startAutoRefresh(interval);
    </script>
    
