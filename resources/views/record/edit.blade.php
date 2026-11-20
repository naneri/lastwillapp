<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Your record') }}</h2>
    </x-slot>

    @if (session('status') === 'record-updated')
        <div class="alert alert-success">{{ __('Saved.') }}</div>
    @endif

    <div class="alert alert-warning">
        <strong>Don't store actual secrets here.</strong> Passwords, bank
        account numbers, PINs, and similar should never go in this text —
        this content is reachable via emailed links, and email accounts can
        be compromised. Instead, describe <em>where</em> to find things, in a
        way only someone who can reach you in person could use:
        <ul class="mb-0">
            <li>"The password list is in the top drawer of my desk."</li>
            <li>"See the file <code>passwords.kdbx</code> on my home PC — ask my brother, he knows where the master password is written down."</li>
            <li>"Ask my sister, she knows the safe combination."</li>
        </ul>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('record.update') }}" id="record-form">
                @csrf
                @method('patch')

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <x-input-label for="body" value="{{ __('Record') }}" class="mb-0" />
                        <span id="record-counter" class="text-muted small"></span>
                    </div>
                    <div id="record-editor" style="display: none;"></div>
                    <textarea id="body" name="body" rows="12" class="form-control">{{ old('body', $record?->body) }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vditor@4.0.1/dist/index.css">
    <script src="https://cdn.jsdelivr.net/npm/vditor@4.0.1/dist/index.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textarea = document.getElementById('body');
            var editorEl = document.getElementById('record-editor');
            var counterEl = document.getElementById('record-counter');
            var maxVisibleLength = {{ (int) $maxVisibleLength }};

            // Approximates what the server's MaxVisibleTextLength rule
            // measures (Markdown rendered to HTML, tags stripped) by
            // stripping the syntax this editor's restricted toolbar can
            // actually produce — reading contenteditable's own innerText
            // instead picks up invisible characters Vditor uses
            // internally for cursor placement, which overcounts.
            function visibleLength(markdown) {
                var text = markdown
                    .replace(/^#{1,6}\s+/gm, '')
                    .replace(/\*\*([^*]+)\*\*/g, '$1')
                    .replace(/\*([^*]+)\*/g, '$1')
                    .replace(/_([^_]+)_/g, '$1')
                    .replace(/^\s*[-*+]\s+/gm, '')
                    .replace(/^\s*\d+\.\s+/gm, '')
                    .replace(/\[([^\]]*)\]\([^)]*\)/g, '$1')
                    .trim();

                return text.length;
            }

            function updateCounter() {
                var length = visibleLength(vditor.getValue());

                counterEl.textContent = length.toLocaleString() + ' / ' + maxVisibleLength.toLocaleString();
                counterEl.classList.toggle('text-danger', length > maxVisibleLength);
                counterEl.classList.toggle('text-muted', length <= maxVisibleLength);
            }

            // Progressive enhancement: if the CDN script fails to load (no
            // network, ad blocker, etc.), the plain textarea above stays
            // visible and still works — we only hide it once the WYSIWYG
            // editor has actually taken over.
            if (typeof Vditor === 'undefined') {
                return;
            }

            var vditor = new Vditor('record-editor', {
                mode: 'wysiwyg',
                height: 360,
                lang: 'en_US',
                value: textarea.value,
                placeholder: 'Where things are and who to ask...',
                cache: { enable: false },
                toolbar: ['headings', 'bold', 'italic', '|', 'list', 'ordered-list', 'link'],
                input: updateCounter,
                after: function () {
                    // Belt-and-suspenders: the constructor's `value` option
                    // isn't documented as mode-specific, so set it again
                    // explicitly once the editor is actually ready.
                    vditor.setValue(textarea.value);
                    textarea.style.display = 'none';
                    editorEl.style.display = '';
                    updateCounter();
                },
            });

            document.getElementById('record-form').addEventListener('submit', function () {
                textarea.value = vditor.getValue();
            });
        });
    </script>
</x-app-layout>
