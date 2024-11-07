<div class="flex-1 background-app flex flex-col h-full">

    <div class="flex navBar border-b border-black h-12 items-center justify-start p-4 gap-x-4">
        <img src="source/assets/images/profile.png" alt="Profile Photo" class="w-10 h-10 rounded-full">
        <span class="text-white font-semibold">Eddy</span>
    </div>

    <div id="messages" class="flex-1 overflow-y-auto text-white p-4 scrollbar-hide">
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
        <p>Message 2</p>
        <p>Message 1</p>
    </div>

    <div class="flex items-center">
        <input type="text" id="message-content" placeholder="Écrivez votre message..."
               class="flex-1 border border-gray-300 dark:border-gray-700 rounded-md p-2 mr-2">
        <button onclick="sendMessage()"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            Envoyer
        </button>
    </div>
</div>
