@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4 sm:py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">URL Analytics</h1>
            <a href="{{ route('urls.view') }}" class="w-full sm:w-auto text-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                View URLs
            </a>
        </div>

        @if($analyticsBatches->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No analytics data available yet.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($analyticsBatches as $batch)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
                                <h2 class="text-base sm:text-lg font-semibold text-gray-800">
                                    Batch created <span class="local-time block sm:inline text-sm sm:text-base" data-utc="{{ $batch['created_at']->toISOString() }}">
                                        {{ $batch['created_at']->format('M j, Y g:i A') }}
                                    </span>
                                </h2>
                                <div class="flex flex-wrap gap-2 sm:space-x-4 sm:gap-0">
                                    <span class="bg-green-100 text-green-800 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">
                                        {{ $batch['total_clicks'] }} Total {{ Str::plural('Click', $batch['total_clicks']) }}
                                    </span>
                                    <span class="bg-blue-100 text-blue-800 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">
                                        {{ $batch['total_urls'] }} URLs
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Batch ID: {{ substr($batch['batch_id'], 0, 8) }}...</p>
                        </div>

                        <div class="p-4 sm:p-6">
                            @php
                                $urlsWithClicks = $batch['urls']->filter(function($url) {
                                    return $url->analytics->count() > 0;
                                });
                            @endphp

                            @if($urlsWithClicks->isEmpty())
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No clicks recorded for this batch yet.</p>
                                </div>
                            @else
                                <div class="space-y-6 sm:space-y-8">
                                    @foreach($urlsWithClicks as $url)
                                        <div class="border border-gray-200 rounded-lg">
                                            <!-- URL Summary Header -->
                                            <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
                                                    <div class="flex-1 w-full sm:w-auto">
                                                        <div class="text-xs sm:text-sm font-medium mb-2 px-2 py-1 rounded-md bg-gray-100 text-gray-800 break-all">
                                                            Long URL: {{ Str::limit($url->original_url, 40) }}
                                                        </div>
                                                        <div class="text-xs sm:text-sm px-2 py-1 rounded-md bg-blue-100 text-blue-800 break-all">
                                                            Short URL: {{ url($url->short_url) }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-0 sm:ml-4 w-full sm:w-auto">
                                                        <span class="inline-flex items-center justify-center w-full sm:w-auto px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-100 text-blue-800">
                                                            {{ $url->analytics->count() }} {{ Str::plural('click', $url->analytics->count()) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Individual Clicks - Mobile Cards / Desktop Table -->
                                            <div class="block sm:hidden">
                                                <!-- Mobile: Card Layout -->
                                                @foreach($url->analytics->sortByDesc('clicked_at') as $analytic)
                                                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-start">
                                                                <span class="text-xs font-medium text-gray-500">Clicked At:</span>
                                                                <span class="local-time text-xs text-gray-900 text-right"
                                                                      data-utc="{{ $analytic->clicked_at->toISOString() }}">
                                                                    {{ $analytic->clicked_at->format('M j, g:i A') }}
                                                                </span>
                                                            </div>
                                                            <div class="flex justify-between items-start">
                                                                <span class="text-xs font-medium text-gray-500">Location:</span>
                                                                <span class="text-xs text-gray-900 text-right">
                                                                    {{ $analytic->geographic_location ?? 'Unknown' }}
                                                                </span>
                                                            </div>
                                                            <div class="flex justify-between items-start">
                                                                <span class="text-xs font-medium text-gray-500">Browser:</span>
                                                                <span class="text-xs text-gray-900 text-right">
                                                                    {{ $analytic->browser ?? 'Unknown' }}
                                                                </span>
                                                            </div>
                                                            <div class="flex justify-between items-start">
                                                                <span class="text-xs font-medium text-gray-500">IP:</span>
                                                                <span class="text-xs text-gray-900 font-mono text-right">
                                                                    {{ $analytic->ip_address ?? 'Unknown' }}
                                                                </span>
                                                            </div>
                                                            <div class="flex justify-between items-start">
                                                                <span class="text-xs font-medium text-gray-500">Referrer:</span>
                                                                <span class="text-xs text-gray-900 text-right break-all max-w-xs">
                                                                    {{ $analytic->referrer ? Str::limit($analytic->referrer, 20) : 'Direct' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="hidden sm:block">
                                                <!-- Desktop: Table Layout -->
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full">
                                                        <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Clicked At
                                                            </th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Location
                                                            </th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Browser
                                                            </th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                IP Address
                                                            </th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Referrer
                                                            </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach($url->analytics->sortByDesc('clicked_at') as $analytic)
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                        <span class="local-time text-sm text-gray-900"
                                                                              data-utc="{{ $analytic->clicked_at->toISOString() }}">
                                                                            {{ $analytic->clicked_at->format('M j, Y g:i A') }}
                                                                        </span>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                        <span class="text-sm text-gray-500">
                                                                            {{ $analytic->geographic_location ?? 'Unknown' }}
                                                                        </span>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                        <span class="text-sm text-gray-500">
                                                                            {{ $analytic->browser ?? 'Unknown' }}
                                                                        </span>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                        <span class="text-sm text-gray-500 font-mono">
                                                                            {{ $analytic->ip_address ?? 'Unknown' }}
                                                                        </span>
                                                                </td>
                                                                <td class="px-6 py-4">
                                                                        <span class="text-sm text-gray-500 truncate max-w-xs block">
                                                                            {{ $analytic->referrer ? Str::limit($analytic->referrer, 30) : 'Direct' }}
                                                                        </span>
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

                // Format: "Jan 15, 2024 2:30 PM"
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
    </script>
@endsection
