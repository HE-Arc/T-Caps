    <!-- Section des messages avec overflow et hauteur ajustée -->
    <div id="messages" class="flex-1 overflow-y-auto text-white p-4 scrollbar-hide bg-cover bg-center messagesBackground">
        <?php
        // Exemple de messages fictifs (normalement récupérés depuis une base de données)
        $messages = [
            ['content' => 'Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1', 'user_id' => 1],
            ['content' => 'Message 2 Message 2Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1 Message 2Message 2Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1', 'user_id' => 1],
            ['content' => 'Message 2 Message 2Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1 Message 2Message 2Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1Message 1', 'user_id' => 1],
            ['content' => 'Message 2 Message 2Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1 Message 2Message 2Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            ['content' => 'Message 1Message 2', 'user_id' => 1],
            ['content' => 'Message 2Message 2Message 2', 'user_id' => 2],
            // Ajoutez autant de messages que nécessaire
        ];

        // L'ID de l'utilisateur courant (exemple)
        $user_auth_id = 1; // Remplacez par l'ID réel de l'utilisateur connecté

        // Boucle pour afficher les messages
        foreach ($messages as $message) {
            // Si le message appartient à l'utilisateur courant, on l'affiche à droite
            if ($message['user_id'] === $user_auth_id) {
                echo "<div class='flex justify-end mb-2'><p class='max-w-[45%] secondary-background-app text-white p-2 rounded-tl-lg rounded-bl-lg rounded-br-lg'>{$message['content']}</p></div>";
            } else {
                // Sinon, on l'affiche à gauche pour les autres utilisateurs
                echo "<div class='flex justify-start mb-2'><p class='max-w-[45%] tertiary-background-app text-white p-2 rounded-tr-lg rounded-bl-lg rounded-br-lg'>{$message['content']}</p></div>";
            }
        }
        ?>
    </div>

