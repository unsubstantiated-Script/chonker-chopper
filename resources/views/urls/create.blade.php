@extends('layouts.app')

@section('title', 'Free URL Shortener')

@section('content')
    <div class="px-4">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-gray-900 mb-4">
                De-Chonk Your Links
            </h1>
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo"
                 class="h-75 w-auto mx-auto block">
            <p class="text-xl text-gray-600 mb-2">
                Create short, memorable URLs instantly
            </p>
            <p class="text-gray-500">
                No sign-up required • Free forever • Track clicks
            </p>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <form action="{{ route('urls.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <div
                            id="csv-drop-zone"
                            class="w-full px-8 py-12 border-2 border-dashed border-gray-300 rounded-lg text-center cursor-pointer hover:border-blue-400"
                            onclick="document.getElementById('csv-file').click()"
                            ondrop="handleDrop(event)"
                            ondragover="handleDragOver(event)"
                            ondragenter="handleDragEnter(event)"
                            ondragleave="handleDragLeave(event)"
                        >
                            <div class="text-4xl mb-4">😸📄</div>
                            <p class="text-lg text-gray-700 mb-2">Drop CSV file or click to browse</p>
                            <p class="text-sm text-gray-500">CSV with URLs in first column</p>
                        </div>

                        <input
                            type="file"
                            id="csv-file"
                            name="csv_file"
                            accept=".csv"
                            class="hidden"
                            onchange="showFileLoaded(this)"
                        >

                        <script>
                            // Drag and drop handlers
                            function handleDragOver(e) {
                                e.preventDefault();
                                e.stopPropagation();
                            }

                            function handleDragEnter(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                document.getElementById('csv-drop-zone').classList.add('border-blue-500', 'bg-blue-50');
                            }

                            function handleDragLeave(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                document.getElementById('csv-drop-zone').classList.remove('border-blue-500', 'bg-blue-50');
                            }

                            function handleDrop(e) {
                                e.preventDefault();
                                e.stopPropagation();

                                const dropZone = document.getElementById('csv-drop-zone');
                                dropZone.classList.remove('border-blue-500', 'bg-blue-50');

                                const files = e.dataTransfer.files;
                                if (files.length > 0) {
                                    const file = files[0];
                                    if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                                        document.getElementById('csv-file').files = files;
                                        showFileLoaded(document.getElementById('csv-file'));
                                    } else {
                                        alert('Please drop a CSV file');
                                    }
                                }
                            }

                            // File loaded feedback
                            function showFileLoaded(input) {
                                if (input.files && input.files[0]) {
                                    const file = input.files[0];
                                    const dropZone = document.getElementById('csv-drop-zone');

                                    dropZone.innerHTML = `
                                        <div class="text-4xl mb-4">✅</div>
                                        <p class="text-lg text-green-700 font-semibold mb-2">${file.name}</p>
                                        <p class="text-sm text-green-600 mb-2">File loaded successfully!</p>
                                        <button type="button" onclick="clearFile()" class="text-blue-600 hover:text-blue-800 text-sm underline">
                                            Choose different file
                                        </button>
                                    `;

                                    dropZone.classList.remove('border-gray-300');
                                    dropZone.classList.add('border-green-400', 'bg-green-50');
                                }
                            }

                            function clearFile() {
                                document.getElementById('csv-file').value = '';
                                const dropZone = document.getElementById('csv-drop-zone');

                                dropZone.innerHTML = `
                                    <div class="text-4xl mb-4">😸📄</div>
                                    <p class="text-lg text-gray-700 mb-2">Drop CSV file or click to browse</p>
                                    <p class="text-sm text-gray-500">CSV with URLs in first column</p>
                                `;

                                dropZone.classList.remove('border-green-400', 'bg-green-50');
                                dropZone.classList.add('border-gray-300');
                            }
                        </script>


                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-lg hover:from-blue-700 hover:to-purple-700 focus:ring-4 focus:ring-blue-300 font-semibold text-lg transition-all transform hover:scale-105"
                    >
                        🐱 Shorten URLs
                    </button>
                </form>
            </div>

            <!-- Features Section -->
            <div class="mt-16 grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <img src="{{ asset('images/fast_cat.png') }}" alt="{{ config('app.name') }} Logo"
                         class="h-30 w-auto mx-auto block mb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Instant</h3>
                    <p class="text-gray-600 text-sm">Get your short URL in seconds</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('images/computer_cat.png') }}" alt="{{ config('app.name') }} Logo"
                         class="h-30 w-auto mx-auto block mb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Analytics</h3>
                    <p class="text-gray-600 text-sm">Track clicks and performance</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('images/secure_cat.png') }}" alt="{{ config('app.name') }} Logo"
                         class="h-30 w-auto mx-auto block mb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Private</h3>
                    <p class="text-gray-600 text-sm">No registration or data collection</p>
                </div>
            </div>

            @if(session('batch_id'))
                <div class="mt-12 text-center">
                    <div class="bg-blue-50 rounded-lg p-6">
                        <p class="text-blue-800 mb-3">You have shortened URLs in this session!</p>
                        <a href="{{ route('urls.view') }}"
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            View all your URLs
                            <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
