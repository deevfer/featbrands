<x-filament-panels::page>
    <form wire:submit="save" style="display:grid;gap:24px">
        {{ $this->form }}
        <div><x-filament::button type="submit" wire:loading.attr="disabled">Guardar configuración</x-filament::button></div>
    </form>
    <x-filament::section>
        <x-slot name="heading">Preparación para HostGator</x-slot>
        <div style="display:grid;gap:12px;font-size:14px;line-height:1.7">
            <p>Este proyecto y sus dependencias actuales necesitan <strong>PHP 8.4.1 o superior</strong>, tanto en el dominio como en la terminal o los cron jobs. Compruébalo con MultiPHP Manager y <code>php -v</code>. La disponibilidad depende del servidor y del plan.</p>
            <p>El dominio debe servir la carpeta <strong>public/</strong> de Laravel, mantener el resto del proyecto fuera del acceso público y tener un certificado HTTPS válido. Las reglas Apache del proyecto incluyen rutas, compresión y caché de archivos estáticos, condicionadas a que el servidor tenga los módulos.</p>
            <p>Al desplegar configura <code>APP_ENV=production</code>, <code>APP_DEBUG=false</code> y <code>APP_URL</code> con el dominio HTTPS. Ejecuta el setup del proyecto, <code>php artisan optimize</code> y después <code>php artisan seo:audit --verify-public</code>.</p>
            <p>Para actualizaciones periódicas puedes ejecutar <code>php artisan schedule:run</code> cada minuto mediante Cron Jobs de cPanel. El análisis está programado una vez al día y también funciona con el botón del dashboard, sin un proceso permanente.</p>
            <p>Si el hosting o una capa de seguridad bloquea bots, valida las IP oficiales del proveedor junto con su agente antes de crear excepciones. El rastreo para búsqueda de OpenAI usa OAI-SearchBot y es independiente de GPTBot, destinado a entrenamiento.</p>
            <p><a href="{{ \App\Filament\Pages\SeoDashboard::getUrl() }}">Volver al dashboard SEO →</a></p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
