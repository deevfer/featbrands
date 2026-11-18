<article class="blog-card {{ $featured ?? false ? 'blog-card-featured' : '' }}">
    <a href="{{ url($article->path).'?market='.$market }}" class="blog-card-art {{ !$article->cover_image ? 'blog-card-art-type' : '' }}" tabindex="-1" aria-hidden="true">
        @if ($article->cover_image)
            <img src="{{ $article->coverImageUrl() }}" alt="" loading="{{ $featured ?? false ? 'eager' : 'lazy' }}">
        @else
            <span class="blog-card-art-label">{{ $copy['noImage'] }}</span><span class="blog-card-art-letter">f<span><x-site-icon name="blog.card_art">↗</x-site-icon></span></span><span class="blog-card-art-foot">{{ $copy['noImageFoot'] }}</span>
        @endif
        <span class="blog-card-number">{{ $featured ?? false ? $copy['featured'] : $copy['cardLabel'] }}</span>
    </a>
    <div class="blog-card-copy"><div class="blog-card-meta"><time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->locale($locale)->translatedFormat('j M Y') }}</time><span>{{ $article->reading_minutes }} {{ $copy['minutes'] }}</span></div>
        @if ($article->categories->isNotEmpty())<span class="blog-card-category">{{ $article->categories->pluck('name')->join(' / ') }}</span>@endif
        <h3><a href="{{ url($article->path).'?market='.$market }}">{{ $article->title }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt), $featured ?? false ? 230 : 155) }}</p>
        <a class="blog-read-link" href="{{ url($article->path).'?market='.$market }}"><span>{{ $copy['read'] }}</span><span aria-hidden="true"><x-site-icon name="general.arrow_out">↗</x-site-icon></span></a>
    </div>
</article>
