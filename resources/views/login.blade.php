<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" media="(prefers-color-scheme: light)" href="{{ asset('images/favicon-light-zoom.png') }}">
    <link rel="icon" media="(prefers-color-scheme: dark)" href="{{ asset('images/favicon-dark-zoom.png') }}">
    <link rel="alternate icon" href="{{ asset('images/favicon-light-zoom.png') }}">
    
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 flex items-center justify-center min-h-screen px-4">
    <div class="w-full max-w-md space-y-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Masuk ke Akun Anda</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gunakan NIP/NIM dan password yang terdaftar</p>
        </div>
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-5" onsubmit="return validateForm()">
            @csrf
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIM/NIP</label>
                <input type="text" name="identifier" id="identifier" required autofocus
                        value="{{ old('identifier') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm placeholder:text-sm leading-tight py-2 px-3 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                <p id="identifier-error" class="text-red-500 text-sm mt-1 hidden">NIM/NIP harus berupa angka dan tidak boleh kosong.</p>
            </div>

            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <div class="mt-1 relative">
                    <input type="password" name="password" id="password" required
                        class="block w-full pr-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <button type="button" onclick="togglePasswordVisibility()" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path id="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p id="password-error" class="text-red-500 text-sm mt-1 hidden">Password minimal 6 karakter.</p>
            </div>


            {{-- <button type="submit"
                    class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                Masuk
            </button> --}}
            <button type="submit" id="login-button"
                class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <span id="button-text">Masuk</span>
                <svg id="spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                    </path>
                </svg>
            </button>

        </form>
    </div>

    <script>
        function validateForm() {
            const identifier = document.getElementById('identifier');
            const password = document.getElementById('password');
            const identifierError = document.getElementById('identifier-error');
            const passwordError = document.getElementById('password-error');

            let valid = true;

            // Reset errors
            identifierError.classList.add('hidden');
            passwordError.classList.add('hidden');

            // NIM/NIP hanya angka
            if (!/^\d+$/.test(identifier.value.trim())) {
                identifierError.classList.remove('hidden');
                valid = false;
            }

            // Password minimal 6 karakter
            if (password.value.length < 6) {
                passwordError.classList.remove('hidden');
                valid = false;
            }

            // Jika valid, ubah tombol jadi loading
            if (valid) {
                const button = document.getElementById("login-button");
                const buttonText = document.getElementById("button-text");
                const spinner = document.getElementById("spinner");

                buttonText.textContent = "Memproses...";
                spinner.classList.remove("hidden");
                button.disabled = true; // Biar gak bisa diklik lagi
            }
            
            return valid;
        }

         function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.975 9.975 0 011.934-3.33M6.455 6.455A9.973 9.973 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.058 5.168M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18"/>
                `;
            } else {
                passwordInput.type = "password";
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>
</html>
