<div class="flex bottom-0 left-0 right-0 justify-center p-4 border-t border-black">
    <div class="flex w-3/4 items-center">
        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-file-modal')"
            class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/add.png')}}" alt="Icone" class="h-6 w-6">
        </button>
        <input type="text" id="message-content" class="flex-1 secondary-background-app text-white border-none rounded-3xl p-2 ml-2 mr-2" onkeypress="if(event.key === 'Enter') { sendMessage() }">
        <button onclick="sendMessage()" class="rounded-full secondary-background-app p-2 flex items-center justify-center">
            <img src="{{asset('source/assets/images/send.png')}}" alt="Icone" class="h-6 w-6">
        </button>
        <x-modal name="create-file-modal" focusable>
            <form method="post" class="p-6" x-data="capsuleForm()" id="create-file-modal-form" @submit.prevent="submitForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="discussion-id" name="discussion_id" value="CHAT_ID">
                <h3 class="text-lg font-semibold text-gray-300 mb-5">Créer une capsule</h3>
                <!-- Champ pour le fichier de la capsule -->
                <div class="flex items-center justify-center bg-gray-800">
                    <div class="border-dashed border-4 border-gray-500 rounded-lg p-8 bg-gray-900 hover:bg-gray-700 transition duration-300" ondrop="handleDrop(event)" ondragover="handleDragOver(event)">
                        <label for="file" class="flex flex-col items-center justify-center cursor-pointer">
                            <div id="file-info">
                                <p class="text-gray-300 font-medium">Glissez et déposez votre fichier ici ou</p>
                                <p class="text-blue-400 underline">cliquez pour sélectionner un fichier</p>
                            </div>
                        </label>
                        <!-- Ajout de l'attribut 'required' pour le fichier -->
                        <input id="file" name="file" type="file" class="hidden" onchange="updateFileName()" x-model="file" required>
                    </div>
                </div>
                <script>
                    function updateFileName() {
                        const fileInput = document.getElementById('file');
                        const fileInfo = document.getElementById('file-info');

                        if (fileInput.files.length > 0) {
                            const fileName = fileInput.files[0].name;
                            fileInfo.innerHTML = `<p class='text-green-400 font-medium'>Fichier sélectionné : ${fileName}</p>`;
                        }
                    }

                    function handleDragOver(event) {
                        event.preventDefault();
                    }

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
                </script>
                <!-- Champ pour le message de la capsule -->
                <div class="mb-4">
                    <x-input-label for="message" value="Message" />
                    <x-text-input id="message" name="message" type="text" class="block w-full mt-1" x-model="chatMessage" required />
                    <div class="mb-4">
                        <x-input-label for="date-time" value="Date et heure d'ouverture de la capsule (laisser vide pour ouverture instantanée)" />
                        <input type="datetime-local" id="date-time" name="date_time" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                    </div>
                </div>
                <!-- Boutons d'action -->
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
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

<script>
function capsuleForm() {
    return {
        chatMessage: '',
        file: null,
        submitForm(event) {
            // Aucun besoin de validation ici car HTML fait déjà le travail
            const form = document.getElementById('create-file-modal-form');
            if (!form.checkValidity()) {
                event.preventDefault(); // Empêche l'envoi si le formulaire n'est pas valide
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            // Récupérer dynamiquement l'ID de la discussion depuis le champ caché
            const discussionId = document.getElementById('discussion-id').value;

            // Créer l'objet FormData avec les données du formulaire
            const formData = new FormData(form);

            // Effectuer la requête AJAX avec `fetch`
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
                    // Fermer le modal et réinitialiser le formulaire
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
</script>
