<div class="flex background-app border-b border-black h-12 items-center justify-between p-4 gap-x-4">
    <div class="flex items-center gap-x-4">
        <img src="" alt="Discussion Picture" class="w-10 h-10 rounded-full headerImage">
        <span class="text-white font-semibold headerTitle"></span>
    </div>
    <button id="leave-chat-btn"
        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
        onclick="leaveChatWithLoad()">
        Quitter
    </button>
    <span id="leave-chat-loader" style="display:none;">
        <div class="spinner"></div>
    </span>`;
</div>