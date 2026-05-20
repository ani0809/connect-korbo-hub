@php
    use App\Models\OrderItem;
    use App\Models\Review;
    use Illuminate\Support\Str;

    $sort = request('sort_reviews', 'newest');
    $orderCol = match ($sort) {
        'helpful' => 'helpful_count',
        'rating_high', 'rating_low' => 'rating',
        default => 'created_at',
    };
    $orderDir = $sort === 'rating_low' ? 'asc' : 'desc';

    $reviews = Review::query()
        ->where('product_id', $product->id)
        ->where('is_approved', true)
        ->where('is_rejected', false)
        ->with('user:id,name,avatar')
        ->when(request('rating_filter'), fn ($q) => $q->where('rating', (int) request('rating_filter')))
        ->orderBy($orderCol, $orderDir)
        ->paginate(5, ['*'], 'review_page');

    $ratingBreakdown = Review::query()
        ->where('product_id', $product->id)
        ->where('is_approved', true)
        ->where('is_rejected', false)
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->orderByDesc('rating')
        ->pluck('count', 'rating')
        ->toArray();

    $totalReviews = (int) array_sum($ratingBreakdown);
    $avgRating = $totalReviews > 0
        ? collect($ratingBreakdown)->sum(fn ($count, $rating) => (int) $rating * (int) $count) / $totalReviews
        : 0;

    $canReview = auth()->check()
        && setting('reviews_enabled', true)
        && OrderItem::query()
            ->where('is_reviewed', false)
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', auth()->id())->where('order_status', 'delivered'))
            ->exists();

    $userReview = auth()->check()
        ? Review::query()->where(['product_id' => $product->id, 'user_id' => auth()->id()])->first()
        : null;

    $pendingItemId = $pendingReviewOrderItem?->id;
@endphp

@if(setting('reviews_enabled', true))
<div class="product-reviews-section" id="reviews">
    <div class="reviews-header">
        <h3 class="reviews-title">
            Customer Reviews
            <span class="reviews-count">({{ $totalReviews }})</span>
        </h3>
        @if($canReview && ! $userReview)
            <button type="button" class="btn-write-review" @click="reviewFormOpen = true">Write a Review</button>
        @endif
    </div>

    <div class="rating-summary" x-data="{ activeFilter: null }">
        <div class="rating-average">
            <div class="avg-number">{{ number_format($avgRating, 1) }}</div>
            <div class="avg-stars">
                @for($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= round($avgRating) ? 'filled' : 'empty' }}">★</span>
                @endfor
            </div>
            <div class="avg-label">
                Based on {{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}
            </div>
        </div>
        <div class="rating-bars">
            @for($star = 5; $star >= 1; $star--)
                @php
                    $count = (int) ($ratingBreakdown[$star] ?? 0);
                    $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                @endphp
                @php $rfParams = array_filter(array_merge(request()->query(), ['rating_filter' => $star]), fn ($v) => $v !== null && $v !== ''); @endphp
                <div class="rating-bar-row {{ (string) request('rating_filter') === (string) $star ? 'active' : '' }}"
                     onclick="window.location='{{ request()->url() }}{{ $rfParams ? '?' . http_build_query($rfParams) : '' }}'">
                    <span class="bar-label">{{ $star }} ★</span>
                    <div class="bar-track">
                        <div class="bar-fill" data-width="{{ round($percentage, 2) }}"></div>
                    </div>
                    <span class="bar-count">{{ $count }}</span>
                </div>
            @endfor
            @if(request('rating_filter'))
                @php $clearQ = request()->except('rating_filter'); @endphp
                <a href="{{ request()->url() }}{{ count($clearQ) ? '?' . http_build_query($clearQ) : '' }}" class="clear-filter">× Clear filter</a>
            @endif
        </div>
    </div>

    @if($reviews->total() > 0)
    <div class="reviews-toolbar">
        <span class="showing-text">
            Showing {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
        </span>
        <div class="sort-select-wrapper flex items-center gap-2">
            <label for="sort-reviews">Sort by:</label>
            <select id="sort-reviews" class="sort-select" onchange="(function(sel){ const u = new URL(window.location.href); u.searchParams.set('sort_reviews', sel.value); window.location = u.toString(); })(this)">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="helpful" {{ $sort === 'helpful' ? 'selected' : '' }}>Most Helpful</option>
                <option value="rating_high" {{ $sort === 'rating_high' ? 'selected' : '' }}>Highest Rated</option>
                <option value="rating_low" {{ $sort === 'rating_low' ? 'selected' : '' }}>Lowest Rated</option>
            </select>
        </div>
    </div>
    @endif

    <div class="reviews-list">
        @forelse($reviews as $review)
            <div class="review-card" id="review-{{ $review->id }}">
                <div class="review-meta">
                    <div class="reviewer-avatar">
                        @if($review->user?->avatar)
                            <img src="{{ asset('storage/'.$review->user->avatar) }}" alt="{{ $review->user->name }}">
                        @else
                            <div class="avatar-initials">{{ strtoupper(Str::substr($review->user?->name ?? 'A', 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="reviewer-info flex-1">
                        <div class="reviewer-name font-medium">
                            {{ $review->user?->name ?? 'Anonymous' }}
                            @if($review->is_verified_purchase)
                                <span class="verified-badge">Verified Purchase</span>
                            @endif
                        </div>
                        <div class="review-date text-sm text-[hsl(var(--muted-foreground))]">@datetime($review->created_at)</div>
                    </div>
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star {{ $i <= $review->rating ? 'filled' : 'empty' }}">★</span>
                        @endfor
                    </div>
                </div>
                @if($review->title && setting('review_allow_title', true))
                    <h4 class="review-title">{{ $review->title }}</h4>
                @endif
                <p class="review-body">{{ $review->comment }}</p>
                @if(setting('review_allow_images', true) && $review->images && count($review->images) > 0)
                    <div class="review-images">
                        @foreach($review->images as $img)
                            <a href="{{ asset('storage/'.$img) }}" data-lightbox="review-{{ $review->id }}" class="review-image-thumb">
                                <img src="{{ asset('storage/'.$img) }}" alt="Review image">
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($review->reply)
                    <div class="review-reply">
                        <div class="reply-header flex justify-between text-sm mb-1">
                            <span class="reply-label">Seller / Store Response</span>
                            <span class="reply-date text-[hsl(var(--muted-foreground))]">@datetime($review->reply_at)</span>
                        </div>
                        <p class="reply-body text-[hsl(var(--foreground))]">{{ $review->reply }}</p>
                    </div>
                @endif
                @if(setting('review_helpfulness_enabled', true))
                <div class="review-helpful" x-data="{
                    count: {{ (int) $review->helpful_count }},
                    voted: localStorage.getItem('review_vote_{{ $review->id }}'),
                    async vote(type) {
                        if (this.voted) return;
                        const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                        const res = await fetch('{{ url('/reviews/'.$review->id.'/helpful') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ type })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.voted = type;
                            if (type === 'yes') this.count = data.count ?? this.count + 1;
                            localStorage.setItem('review_vote_{{ $review->id }}', type);
                        }
                    }
                }">
                    <span class="helpful-text">Was this helpful?</span>
                    <button type="button" @click="vote('yes')" :class="{ 'voted': voted === 'yes' }" class="helpful-btn">Yes (<span x-text="count"></span>)</button>
                    <button type="button" @click="vote('no')" :class="{ 'voted': voted === 'no' }" class="helpful-btn">No</button>
                </div>
                @endif
            </div>
        @empty
            <div class="no-reviews">
                <div class="no-reviews-icon text-3xl mb-2">⭐</div>
                <h4 class="font-semibold">No reviews yet</h4>
                <p>Be the first to review this product!</p>
            </div>
        @endforelse
    </div>

    {{ $reviews->appends(request()->query())->links('frontend.partials.pagination') }}

    <div class="write-review-section" x-show="reviewFormOpen" x-transition id="write-review" x-cloak>
        @auth
            @if($canReview && $pendingItemId)
                <h3 class="font-semibold text-lg mb-3">Write Your Review</h3>
                <form action="{{ route('account.orders.review', $pendingItemId) }}" method="POST" enctype="multipart/form-data" x-data="{ rating: 0, hover: 0, comment: '' }">
                    @csrf
                    <div class="rating-input-group mb-4">
                        <label class="block font-medium mb-1">Your Rating *</label>
                        <div class="star-input">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star-input-item"
                                      @mouseenter="hover = {{ $i }}"
                                      @mouseleave="hover = 0"
                                      @click="rating = {{ $i }}; $refs.ratingInput.value = {{ $i }}"
                                      :class="{ 'active': hover >= {{ $i }} || rating >= {{ $i }} }">★</span>
                            @endfor
                            <input type="hidden" name="rating" x-ref="ratingInput" required>
                            <p class="rating-label-hint w-full" x-text="['','Poor','Fair','Good','Very Good','Excellent'][hover || rating] || ''"></p>
                        </div>
                    </div>
                    @if(setting('review_allow_title', true))
                    <div class="form-group mb-3">
                        <label class="block text-sm mb-1">Review Title</label>
                        <input type="text" name="title" maxlength="100" class="form-input" placeholder="Summarize your experience">
                    </div>
                    @endif
                    <div class="form-group mb-3">
                        <label class="block text-sm mb-1">Your Review *</label>
                        <textarea name="comment" rows="5" required minlength="10" maxlength="1000" class="form-textarea" x-model="comment" placeholder="Tell others about your experience…"></textarea>
                        <div class="char-count text-xs text-[hsl(var(--muted-foreground))] mt-1"><span x-text="comment.length"></span>/1000</div>
                    </div>
                    @if(setting('review_allow_images', true))
                    <div class="form-group mb-3">
                        <label class="block text-sm mb-1">Add Photos (optional)</label>
                        <input type="hidden" id="review_image_media_ids" name="image_media_ids" value="">
                        <button
                            type="button"
                            class="btn-primary inline-flex items-center justify-center px-3 py-2 rounded"
                            data-toggle="media-picker"
                            data-input="#review_image_media_ids"
                            data-preview="#reviewImagePickerPreview"
                            data-type="image"
                            data-multiple="true"
                        >
                            Select from Media Manager
                        </button>
                        <div id="reviewImagePickerPreview" class="shopadmin-editor-media-grid mt-2"></div>
                        <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">Or upload directly</p>
                        <input type="file" name="images[]" multiple accept="image/*" class="block text-sm">
                        <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">Max {{ (int) setting('review_max_images', 5) }} photos, 2MB each</p>
                    </div>
                    @endif
                    <button type="submit" class="btn-submit-review">Submit Review</button>
                </form>
            @elseif($userReview)
                <div class="already-reviewed text-[hsl(var(--muted-foreground))]">
                    You have already reviewed this product.
                    <a href="#review-{{ $userReview->id }}" class="link-primary font-medium">View your review</a>
                </div>
            @else
                <div class="cannot-review text-[hsl(var(--muted-foreground))]">You can only review products you have purchased and received.</div>
            @endif
        @else
            <div class="login-to-review">
                <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center">Login to Write a Review</a>
            </div>
        @endauth
    </div>
</div>
@endif
