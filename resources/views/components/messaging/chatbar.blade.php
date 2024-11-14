<div class="flex bottom-0 left-0 right-0 justify-center p-4 border-t border-black">
    <div class="flex w-3/4 items-center">
        <button onclick="sendFile()"
            class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/add.png')}}" alt="Icone" class="h-6 w-6">
        </button>
        <input type="text" id="message-content" class="flex-1 secondary-background-app text-white border-none rounded-3xl p-2 ml-2 mr-2">
        <button onclick="sendMessage()"
            class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/send.png')}}" alt="Icone" class="h-6 w-6">
        </button>
    </div>
</div>
