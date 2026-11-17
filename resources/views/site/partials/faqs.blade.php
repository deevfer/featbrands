@if ($faqs)
    <section class="blog-faqs" aria-labelledby="preguntas-relacionadas"><h2 id="preguntas-relacionadas">{{ ($faqLocale ?? $locale) === 'en' ? 'Related questions' : 'Preguntas relacionadas' }}</h2>@foreach ($faqs as $faq)<h3>{{ $faq['question'] }}</h3><p>{{ $faq['answer'] }}</p>@endforeach</section>
@endif
