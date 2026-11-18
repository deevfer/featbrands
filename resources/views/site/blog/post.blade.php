@extends('site.layout')

@section('content')
<main id="main-content" class="specialization blog-page blog-post-page">
    <article lang="{{ $post->locale }}">
        <header class="blog-article-heading"><div class="service-width"><a class="blog-back" href="{{ $blogUrl }}">← {{ $copy['back'] }}</a><div class="blog-article-eyebrow"><span class="service-eyebrow">{{ $copy['masthead'] }} @if ($post->categories->isNotEmpty()) / {{ $post->categories->pluck('name')->join(' / ') }} @endif</span><span>{{ $post->reading_minutes }} {{ $copy['minutes'] }}</span></div><h1>{{ $post->title }}</h1><div class="blog-article-byline"><span>{{ $copy['writtenBy'] }} {{ $post->author }}</span><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($locale)->translatedFormat('j F Y') }}</time></div></div></header>
        <div class="blog-reading-progress" aria-hidden="true"><span data-reading-progress></span></div>
        <div class="blog-reading-layout service-width"><aside class="blog-reading-aside">@if ($articleHeadings)<details class="blog-toc" open><summary>{{ $copy['contents'] }} <span aria-hidden="true">↙</span></summary><nav aria-label="{{ $copy['contents'] }}">@foreach ($articleHeadings as $heading)<a href="#{{ $heading['id'] }}">{{ $heading['text'] }}</a>@endforeach</nav></details>@else<p class="blog-reading-note">{{ $copy['note'] }}</p>@endif<div class="blog-side-mark" aria-hidden="true">f.</div></aside>
            <div class="blog-reading-main">@if ($locale !== $post->locale)<p class="blog-language-note" role="note">{{ $post->locale === 'es' ? $copy['originalSpanish'] : $copy['originalLanguage'] }}</p>@endif
                <div class="blog-prose" data-blog-content>{!! $articleHtml !!}</div>@include('site.partials.faqs', ['faqLocale' => $post->locale])
                @if ($post->tags)<nav class="blog-tags" aria-label="{{ $copy['tag'] }}">@foreach ($post->tags as $tag)<a href="{{ url(($post->locale === 'en' ? '/en' : '').'/blog/'.$tag['type'].'/'.$tag['slug']).'?market='.$market }}">#{{ $tag['name'] }}</a>@endforeach</nav>@endif
                <div class="blog-share"><span>{{ $copy['share'] }}</span><div><a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">in ↗</a><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">f ↗</a><a href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" aria-label="X">X ↗</a><button type="button" data-blog-copy data-url="{{ $canonicalUrl }}" data-label="{{ $copy['copyLink'] }}" data-copied="{{ $copy['copied'] }}">{{ $copy['copyLink'] }}</button><button type="button" data-blog-print>{{ $copy['print'] }}</button></div><span class="blog-screen-reader" role="status" data-copy-status></span></div>
            </div>
        </div>
    </article>
    @if ($related->isNotEmpty())<section class="blog-related service-width"><p class="service-eyebrow">{{ $copy['relatedKicker'] }}</p><h2>{{ $copy['related'] }}</h2><div class="blog-collection">@foreach ($related as $article)@include('site.blog.card', ['featured' => false])@endforeach</div></section>@endif
    @include('site.blog.closing')
</main>
@endsection
