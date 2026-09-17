import { initializeContentEditors } from './contentEditor';
import { initializeFieldBuilder } from './fieldBuilder';
import { initializeNavigation } from './navigation';

const initializePage = (): void => {
    // Este modulo se carga en todas las paginas.
    // Primero activamos el comportamiento comun del layout.
    initializeNavigation();

    // Si existe el constructor manual, se inicializa.
    // Si no existe, la funcion sale sin hacer nada.
    initializeFieldBuilder();

    // TinyMCE debe arrancar siempre de forma independiente.
    // Esto cubre tanto paginas renderizadas por Laravel como vistas con builder.
    initializeContentEditors();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePage, { once: true });
} else {
    initializePage();
}
