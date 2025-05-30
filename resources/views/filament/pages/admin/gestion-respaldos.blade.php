<x-filament-panels::page>

    <div class="px-4 py-2_5 md:px-6_to_8">
        <h2 class="text-2xl font-bold tracking-tight">Operaciones de Respaldo de Base de Datos</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Desde esta sección puede iniciar manualmente la creación de un respaldo de la base de datos
            o ejecutar el proceso de limpieza de respaldos antiguos según la política de retención configurada.
        </p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            La configuración de qué se respalda y dónde se guarda se gestiona en el archivo
            <code>config/backup.php</code>.
            Los respaldos automáticos se deben configurar en el programador de tareas del servidor.
        </p>

        {{-- Si necesitas listar los archivos, esa parte es la que daba problemas.
             Por ahora, esta página solo tendrá los botones de acción en la cabecera.
             Si la tabla es crucial, tendríamos que volver a depurar esa parte específicamente. --}}

        @if (session()->has('artisan_command_output'))
            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-900 rounded-md">
                <h3 class="text-lg font-medium">Salida del Último Comando:</h3>
                <pre class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ session('artisan_command_output') }}</pre>
            </div>
        @endif

    </div>

</x-filament-panels::page>
