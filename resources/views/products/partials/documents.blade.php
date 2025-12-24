<!-- Downloadable Files Section -->
@if ($documents->count() > 0)
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
        <h3 class="text-xl font-bold text-slate-900 mb-4">Documents & Files</h3>
        <div class="space-y-3">
            @foreach ($documents as $document)
                @php
                    $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                    $iconClasses = 'bg-slate-50 border-2 border-slate-200';
                    $iconColor = 'text-slate-500';
                    if($extension === 'pdf') {
                        $iconClasses = 'bg-red-50 border-2 border-red-200';
                        $iconColor = 'text-red-600';
                    } elseif(in_array($extension, ['doc', 'docx'])) {
                        $iconClasses = 'bg-blue-50 border-2 border-blue-200';
                        $iconColor = 'text-blue-600';
                    } elseif(in_array($extension, ['xls', 'xlsx'])) {
                        $iconClasses = 'bg-green-50 border-2 border-green-200';
                        $iconColor = 'text-green-600';
                    }
                @endphp
                <a href="{{ $document->file_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-blue-300 transition group">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 {{ $iconClasses }}">
                        <svg class="w-7 h-7 {{ $iconColor }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            <path d="M8,10H16V12H8V10M8,13H13V15H8V13M8,16H16V18H8V16Z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-slate-900 group-hover:text-blue-600 transition truncate">
                            {{ $document->file_name }}
                        </div>
                        <div class="text-sm text-slate-500 mt-1">
                            {{ strtoupper($extension) }} Document
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

