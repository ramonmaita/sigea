<div>
    @if($pdfUrl)
        <iframe src="{{ $pdfUrl }}" width="100%" height="600px" frameborder="0">
            Tu navegador no soporta iframes. Puedes descargar el PDF <a href="{{ $pdfUrl }}">aquí</a>.
        </iframe>
    @else
        <p>No se pudo cargar la URL del PDF.</p>
    @endif
</div>
