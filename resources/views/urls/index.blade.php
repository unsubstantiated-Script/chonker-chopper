@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4 sm:py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Your Shortened URLs</h1>
            <a href="{{ route('urls.upload') }}" class="w-full sm:w-auto text-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Create New Batch
            </a>
        </div>

        @if($urlBatches->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-base sm:text-lg">No URLs have been shortened yet.</p>
                <a href="{{ route('urls.upload') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg">
                    Get Started
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($urlBatches as $batch)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
                                <h2 class="text-base sm:text-lg font-semibold text-gray-800">
                                    Batch created <span class="local-time block sm:inline text-sm sm:text-base" data-utc="{{ $batch['created_at']->toISOString() }}">
                                        {{ $batch['created_at']->format('M j, Y g:i A') }}
                                    </span>
                                </h2>
                                <span class="bg-blue-100 text-blue-800 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">
                                    {{ $batch['total_urls'] }} URLs
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Batch ID: {{ substr($batch['batch_id'], 0, 8) }}...</p>
                        </div>

                        <div class="p-4 sm:p-6">
                            <!-- Mobile: Card Layout -->
                            <div class="block sm:hidden space-y-4">
                                @foreach($batch['urls'] as $url)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-xs font-medium text-gray-500 block mb-1">Original URL:</label>
                                                <div class="text-xs bg-gray-100 p-2 rounded break-all">
                                                    {{ Str::limit($url->original_url, 50) }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500 block mb-1">Short URL:</label>
                                                <div class="flex items-center justify-between bg-blue-50 p-2 rounded">
                                                    <a href="{{ url($url->short_url) }}" target="_blank"
                                                       class="text-xs text-blue-600 hover:text-blue-800 break-all flex-1">
                                                        {{ url($url->short_url) }}
                                                    </a>
                                                    <button onclick="copyToClipboard('{{ url($url->short_url) }}')"
                                                            class="ml-2 text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Desktop: Table Layout -->
                            <div class="hidden sm:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Original URL
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Short URL
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($batch['urls'] as $url)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">
                                                    {{ Str::limit($url->original_url, 50) }}
                                                </code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ url($url->short_url) }}" target="_blank"
                                                   class="text-blue-600 hover:text-blue-800 truncate max-w-xs block">
                                                    <code>
                                                        {{ url($url->short_url) }}
                                                    </code>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="copyToClipboard('{{ url($url->short_url) }}')"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Copy
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convert all UTC timestamps to local time
            document.querySelectorAll('.local-time').forEach(function(element) {
                const utcTime = element.getAttribute('data-utc');
                const localTime = new Date(utcTime);

                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                };

                element.textContent = localTime.toLocaleDateString('en-US', options);
            });
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard!');
            });
        }
    </script>
@endsection
