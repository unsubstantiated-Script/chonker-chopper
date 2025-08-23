@props(['url'])

<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 mb-4">
    <div class="space-y-4">
        <!-- Short URL Section -->
        <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">
                Short URL
            </label>
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                <code class="text-lg font-mono text-blue-600 flex-1">{{ url($url->short_url) }}</code>
                <div class="flex space-x-2 ml-4">
                    <button
                        onclick="copyToClipboard('{{ url($url->short_url) }}', this)"
                        class="text-gray-500 hover:text-blue-600 p-1 rounded transition-colors"
                        title="Copy to clipboard"
                    >
                        📋
                    </button>
                    <a
                        href="{{ url($url->short_url) }}"
                        target="_blank"
                        class="text-gray-500 hover:text-blue-600 p-1 rounded transition-colors"
                        title="Visit URL"
                    >
                        🔗
                    </a>
                </div>
            </div>
        </div>

        <!-- Original URL Section -->
        <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">
                Original URL
            </label>
            <p class="text-sm text-gray-700 break-all bg-gray-50 rounded p-2">
                {{ $url->original_url }}
            </p>
        </div>

        <!-- Stats -->
        <div class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t border-gray-100">
            <span>Created {{ $url->created_at->diffForHumans() }}</span>
            @if(isset($url->analytics_count))
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                    {{ $url->analytics_count }} clicks
                </span>
            @endif
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            // Change button text temporarily
            const original = button.innerHTML;
            button.innerHTML = '✅';
            button.style.color = '#10B981';

            setTimeout(() => {
                button.innerHTML = original;
                button.style.color = '';
            }, 1500);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            button.innerHTML = '✅';
            setTimeout(() => button.innerHTML = '📋', 1500);
        });
    }
</script>
