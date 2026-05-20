@php
    use App\Models\ProductQuestion;
    use Illuminate\Support\Str;

    $questions = ProductQuestion::query()
        ->where('product_id', $product->id)
        ->where('is_approved', true)
        ->with([
            'user:id,name',
            'answers' => fn ($q) => $q->where('is_approved', true)->with(['user:id,name', 'seller:id,shop_name'])->orderBy('created_at'),
        ])
        ->withCount(['answers' => fn ($q) => $q->where('is_approved', true)])
        ->orderByDesc('helpful_count')
        ->paginate(5, ['*'], 'qa_page');
@endphp

@if(setting('qa_enabled', true))
<div class="product-qa-section" id="qna" data-product-id="{{ $product->id }}">
    <div class="qa-header">
        <h3>Questions &amp; Answers <span class="qa-count">({{ $questions->total() }})</span></h3>
        @if(setting('qa_logged_in_only', false) && !auth()->check())
            <a href="{{ route('login') }}" class="btn-ask-question inline-flex items-center">Login to Ask</a>
        @elseif(!setting('qa_guest_requires_login', false) || auth()->check())
            <button type="button" class="btn-ask-question" @click="askFormOpen = !askFormOpen">Ask a Question</button>
        @else
            <a href="{{ route('login') }}" class="btn-ask-question inline-flex items-center">Login to Ask</a>
        @endif
    </div>

    @if(!setting('qa_logged_in_only', false) || auth()->check())
    <div class="ask-question-form border border-dashed border-gray-200 rounded-xl p-4 mb-6"
         x-show="askFormOpen"
         x-transition
         x-cloak
         x-data="askQuestionForm({{ $product->id }})">
        <h4 class="font-semibold mb-2">Ask a Question</h4>
        <p class="qa-note text-sm text-gray-600 mb-3">Your question will be answered by the seller or other customers.</p>

        @guest
            <div class="form-group mb-3">
                <label class="block text-sm mb-1">Your Name *</label>
                <input type="text" x-model="guestName" class="form-input" placeholder="Your name" autocomplete="name">
            </div>
            <div class="form-group mb-3">
                <label class="block text-sm mb-1">Email *</label>
                <input type="email" x-model="guestEmail" class="form-input" placeholder="you@example.com" autocomplete="email">
            </div>
        @endguest

        <div class="form-group mb-3">
            <label class="block text-sm mb-1">Your Question *</label>
            <textarea x-model="question" rows="3" class="form-textarea" placeholder="What would you like to know?" maxlength="500" minlength="10"></textarea>
            <div class="char-count text-xs text-gray-500 mt-1"><span x-text="question.length"></span>/500</div>
        </div>

        <div class="qa-search-tip mb-3">
            <input type="text" class="form-input" placeholder="Search existing questions…" x-model="searchQuery" @input.debounce.300ms="searchQa()">
        </div>
        <div class="qa-search-results text-sm mb-3" x-show="searchResults.length > 0" x-cloak>
            <p class="font-medium mb-1">Similar questions:</p>
            <template x-for="result in searchResults" :key="result.id">
                <div class="search-result-item py-1 px-2 rounded hover:bg-gray-50 cursor-pointer flex justify-between gap-2" @click="scrollToQuestion(result.id)">
                    <span x-text="result.question"></span>
                    <span class="text-gray-500 text-xs shrink-0" x-text="result.answers_count + ' answers'"></span>
                </div>
            </template>
        </div>

        <button type="button" class="btn-primary inline-flex items-center justify-center" @click="submitQuestion()" :disabled="submitting">
            <span x-show="!submitting">Submit Question</span>
            <span x-show="submitting">Submitting…</span>
        </button>
    </div>
    @endif

    <div class="questions-list">
        @forelse($questions as $q)
            <div class="question-item" id="question-{{ $q->id }}">
                <div class="question-content">
                    <span class="q-badge">Q</span>
                    <div class="q-text-wrapper flex-1">
                        <p class="q-text">{{ $q->question }}</p>
                        <div class="q-meta text-xs text-gray-500 flex flex-wrap gap-2 mt-1">
                            <span class="q-asker">{{ $q->user?->name ?? $q->guest_name ?? 'Guest' }}</span>
                            <span class="q-date">{{ $q->created_at->diffForHumans() }}</span>
                            <span class="q-answers-count">{{ $q->answers_count }} {{ Str::plural('answer', $q->answers_count) }}</span>
                        </div>
                    </div>
                </div>

                @php $ansList = $q->answers; @endphp
                <div class="answers-list ml-10 mt-3" x-data="{ openAll: false }">
                    @foreach($ansList as $idx => $answer)
                        <div class="answer-item" x-show="openAll || {{ $idx }} < 2" x-transition>
                            <span class="a-badge {{ $answer->is_admin ? 'admin' : ($answer->seller_id ? 'seller' : '') }}">A</span>
                            <div class="a-content flex-1">
                                <p class="a-text text-gray-800">{{ $answer->answer }}</p>
                                <div class="a-meta flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
                                    @if($answer->is_admin)
                                        <span class="a-responder font-medium text-blue-700">Store Admin</span>
                                    @elseif($answer->seller_id && $answer->seller)
                                        <span class="a-responder font-medium text-emerald-700">{{ $answer->seller->shop_name }}</span>
                                    @else
                                        <span class="a-responder">{{ $answer->user?->name ?? 'Customer' }}</span>
                                    @endif
                                    <span class="a-date">{{ $answer->created_at->diffForHumans() }}</span>
                                    <button type="button" class="helpful-btn" data-answer-id="{{ $answer->id }}">Helpful (<span class="helpful-count">{{ $answer->helpful_count }}</span>)</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($ansList->count() > 2)
                        <button type="button" class="text-sm text-blue-600 mt-2" @click="openAll = true" x-show="!openAll">Show all {{ $ansList->count() }} answers</button>
                    @endif
                </div>

                @auth
                <div class="add-answer-form mt-3 ml-10" x-data="{ open: false, answer: '' }">
                    <button type="button" class="text-sm font-medium text-blue-600" @click="open = !open">+ Add Your Answer</button>
                    <div x-show="open" x-transition class="mt-2" x-cloak>
                        <textarea x-model="answer" rows="3" class="form-textarea" placeholder="Share your knowledge…"></textarea>
                        <button type="button" class="btn-primary btn-sm mt-2 text-sm px-3 py-2" @click="submitAnswerToQuestion({{ $q->id }}, answer)">Submit Answer</button>
                    </div>
                </div>
                @endauth
            </div>
        @empty
            <div class="no-questions">
                <div class="no-qa-icon text-3xl mb-2">❓</div>
                <p>No questions yet. Be the first to ask!</p>
            </div>
        @endforelse
    </div>

    {{ $questions->appends(request()->query())->links('frontend.partials.pagination') }}
</div>

<script>
(function () {
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const storeUrl = @json(route('qa.questions.store'));
    const searchUrl = @json(route('qa.search'));
    const answersUrl = @json(route('qa.answers.store'));
    const helpfulBase = @json(url('/qa/answers'));
    const isGuest = @json(! auth()->check());

    window.askQuestionForm = function (productId) {
        return {
            question: '',
            guestName: '',
            guestEmail: '',
            submitting: false,
            searchQuery: '',
            searchResults: [],
            async submitQuestion() {
                if (!this.question.trim() || this.question.length < 10) {
                    alert('Please enter a question (at least 10 characters).');
                    return;
                }
                if (isGuest && (!this.guestName.trim() || !this.guestEmail.trim())) {
                    alert('Please enter your name and email.');
                    return;
                }
                this.submitting = true;
                try {
                    const body = { product_id: productId, question: this.question };
                    if (isGuest) {
                        body.guest_name = this.guestName;
                        body.guest_email = this.guestEmail;
                    }
                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.question = '';
                        if (window.__showToast) window.__showToast(data.message || 'Saved', 'success');
                        if (data.auto_approved) setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert(data.message || 'Could not submit question.');
                    }
                } finally {
                    this.submitting = false;
                }
            },
            async searchQa() {
                if (this.searchQuery.length < 3) {
                    this.searchResults = [];
                    return;
                }
                const u = new URL(searchUrl, window.location.origin);
                u.searchParams.set('q', this.searchQuery);
                u.searchParams.set('product_id', String(productId));
                const res = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.searchResults = data.questions || [];
            },
            scrollToQuestion(id) {
                document.getElementById('question-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.searchResults = [];
                this.searchQuery = '';
            },
        };
    };

    window.submitAnswerToQuestion = async function (questionId, answer) {
        if (!answer || !String(answer).trim()) return;
        const res = await fetch(answersUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
            body: JSON.stringify({ question_id: questionId, answer: String(answer).trim() }),
        });
        const data = await res.json();
        if (data.success) window.location.reload();
        else alert(data.message || 'Failed');
    };

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.helpful-btn[data-answer-id]');
        if (!btn) return;
        const id = btn.getAttribute('data-answer-id');
        const key = 'answer_voted_' + id;
        if (localStorage.getItem(key)) return;
        fetch(helpfulBase + '/' + id + '/helpful', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    localStorage.setItem(key, '1');
                    const span = btn.querySelector('.helpful-count');
                    if (span && typeof data.count !== 'undefined') span.textContent = data.count;
                }
            });
    });
})();
</script>
@endif
