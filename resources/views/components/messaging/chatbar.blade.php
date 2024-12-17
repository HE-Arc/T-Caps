<div class="flex bottom-0 left-0 right-0 justify-center p-4 border-t border-black">
    <div class="flex w-3/4 items-center">
        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-file-modal')"
            class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/add.png')}}" alt="Icone" class="h-6 w-6">
        </button>
        <input type="text" id="message-content" class="flex-1 secondary-background-app text-white border-none rounded-3xl p-2 ml-2 mr-2" onkeypress="if(event.key === 'Enter') { sendMessageWithLoader() }">
        <button id="send-message-btn" onclick="sendMessageWithLoader()" class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/send.png')}}" alt="Icone" class="h-6 w-6">
        </button>
        <span id="send-message-loader" style="display:none;">
            <div class="spinner"></div>
        </span>

        <x-modal name="create-file-modal" focusable>
            <form method="post" class="p-6" x-data="capsuleForm()" id="create-file-modal-form" @submit.prevent="submitForm" enctype="multipart/form-data" x-on:click.away="$dispatch('close'); document.getElementById('create-file-modal-form').reset(); document.getElementById('file-info').innerHTML = '<p class=&quot;text-gray-300 font-medium&quot;>Glissez et déposez votre fichier ici ou</p><p class=&quot;text-blue-400 underline&quot;>cliquez pour sélectionner un fichier</p>';">
                @csrf
                <input type="hidden" id="discussion-id" name="discussion_id" value="CHAT_ID">
                <h3 class="text-lg font-semibold text-gray-300 mb-5">Créer une capsule</h3>

                <div class="flex items-center justify-center h-full w-full">
                    <div class="border-dashed border-4 border-gray-500 rounded-lg bg-gray-900 hover:bg-gray-700 transition duration-300" ondrop="handleDrop(event)" ondragover="handleDragOver(event)">
                        <label for="file" class="flex flex-col items-center justify-center cursor-pointer h-full w-full">
                            <div id="file-info" class="text-center mt-6">
                                <p class="text-gray-300 font-medium">Glissez et déposez votre fichier ici ou</p>
                                <p class="text-blue-400 underline">cliquez pour sélectionner un fichier</p>
                            </div>
                        </label>
                        <input id="file" name="file" type="file" class="inset-0 opacity-0 cursor-pointer" accept=".jpeg,.png,.jpg,.gif,.mp3,.mp4,.mov" onchange="updateFileName()" x-model="file" required>
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="message" value="Message" />
                    <x-text-input id="message" name="message" type="text" class="block w-full mt-1" x-model="chatMessage" required />
                    <div class="mb-4">
                        <x-input-label for="date-time" value="Date et heure d'ouverture de la capsule (laisser vide pour ouverture instantanée)" />
                        <input type="datetime-local" id="date-time" name="date_time" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close'); document.getElementById('create-file-modal-form').reset(); document.getElementById('file-info').innerHTML = '<p class=&quot;text-gray-300 font-medium&quot;>Glissez et déposez votre fichier ici ou</p><p class=&quot;text-blue-400 underline&quot;>cliquez pour sélectionner un fichier</p>';">
                        Annuler
                    </x-secondary-button>
                    <x-primary-button class="ml-3" type="submit">
                        Envoyer la capsule
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    </div>
</div>
