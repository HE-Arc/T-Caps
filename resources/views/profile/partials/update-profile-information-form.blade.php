<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Information sur le profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Mettre à jour les informations de profil et l'adresse e-mail de votre compte.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nom d\'utilisateur')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <label for="image-upload">
                <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('/source/assets/avatar/avatar.png') }}"
                    alt="Image" class="avatar" style="cursor: pointer;">
            </label>
            <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Sauvegarder') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400">{{ __('Sauvegardé.') }}</p>
            @endif
        </div>
    </form>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            const avatar = document.querySelector('.avatar');

            reader.onload = function() {
                avatar.src = reader.result;
            };

            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</section>
