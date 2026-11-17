@extends('site.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/blog.css').'?v='.filemtime(public_path('css/blog.css')) }}">
@endpush

@section('content')
<main id="main-content" class="specialization blog-page geo-page">
    <article lang="es">
        <header class="blog-article-heading"><div class="service-width"><h1>{{ $copy['h1'] }}</h1></div></header>
        <div class="service-width geo-body">
            <div class="blog-prose">
                @foreach ($copy['blocks'] as $block)
                    @if ($block['type'] === 'ul')
                        <ul>@foreach ($block['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    @elseif ($block['type'] === 'h2')
                        <h2>{!! $block['html'] !!}</h2>
                    @else
                        <p>{!! $block['html'] !!}</p>
                    @endif
                @endforeach
            </div>
            @include('site.partials.faqs', ['faqLocale' => 'es'])
        </div>
    </article>
</main>
@endsection
