import { initializeContentEditors, removeContentEditors } from './contentEditor';

/**
 * Tipos de campo disponibles en el selector "Tipo".
 * Tenerlo como config aparte hace muy fácil añadir/quitar tipos sin tocar el HTML.
 */
const TIPOS_DE_CAMPO: { value: string; label: string }[] = [
    { value: 'text', label: 'Input de texto' },
    { value: 'textarea', label: 'Textarea' },
    { value: 'number', label: 'Número' },
    { value: 'email', label: 'Email' },
    { value: 'date', label: 'Fecha' },
    { value: 'select', label: 'Select' },
    { value: 'checkbox', label: 'Checkbox' },
    { value: 'file', label: 'Archivo / imagen' },
];

/**
 * Constructor de campos dinámicos.
 *
 * Permite al usuario añadir "filas" donde define nombre + tipo de campo,
 * y genera en tiempo real una vista previa del formulario final que se enviará.
 */
export const initializeFieldBuilder = (): void => {

    // --- 1. Referencias a los elementos fijos del DOM ---
    // Estos elementos existen una sola vez en la página, por eso usamos getElementById.

    const contenedorPrincipal = document.querySelector<HTMLElement>('[data-field-builder]');
    if (!contenedorPrincipal) return; // Si no existe, no hay tabla seleccionada: no hacemos nada.

    const contenedorFilas = contenedorPrincipal.querySelector<HTMLElement>('[data-field-builder-rows]');
    const contenedorPreview = contenedorPrincipal.querySelector<HTMLElement>('[data-field-builder-preview]');
    const botonAgregarCampo = contenedorPrincipal.querySelector<HTMLButtonElement>('[data-add-field]');

    if (!contenedorFilas || !contenedorPreview || !botonAgregarCampo) return;

    let contadorFilas = 0; // Usamos un contador propio en vez de depender de children.length

    /**
     * Lee todas las filas actuales y reconstruye la vista previa del formulario.
     */
    const renderPreview = (): void => {
        // Limpiamos la vista previa antes de reconstruirla
        contenedorPreview.replaceChildren();

        const filas = contenedorFilas.querySelectorAll<HTMLElement>('[data-field-row]');

        if (filas.length === 0) {
            const vacio = document.createElement('p');
            vacio.className = 'muted';
            vacio.textContent = 'Añade un campo para comenzar.';
            contenedorPreview.append(vacio);
            return;
        }

        filas.forEach((fila) => {
            const campoGenerado = construirCampoDesdeFila(fila);
            if (campoGenerado) {
                contenedorPreview.append(campoGenerado);
            }
        });

        // Reiniciamos TinyMCE porque los textarea se han vuelto a crear desde cero
        removeContentEditors();
        initializeContentEditors();
    };

    /**
     * A partir de una fila del "constructor" (nombre + tipo + opciones),
     * genera el <label> con el input real que irá en el formulario.
     */
    const construirCampoDesdeFila = (fila: HTMLElement): HTMLLabelElement | null => {
        // Estos SÍ se repiten por fila, así que no pueden tener id único global.
        // Los buscamos con querySelector limitado a "fila", no a todo el documento.
        const nombreInput = fila.querySelector<HTMLInputElement>('[data-field-name]');
        const tipoInput = fila.querySelector<HTMLSelectElement>('[data-field-type]');
        const opcionesInput = fila.querySelector<HTMLTextAreaElement>('[data-field-options]');

        const nombre = nombreInput?.value.trim() ?? '';
        const tipo = tipoInput?.value ?? 'text';

        if (!nombre) return null;

        const label = document.createElement('label');
        label.className = 'campo campoFormulario';

        const etiqueta = document.createElement('span');
        etiqueta.className = 'etiquetaCampo';
        etiqueta.textContent = nombre;
        label.append(etiqueta);

        let input: HTMLElement;

        switch (tipo) {
            case 'textarea':
                input = document.createElement('textarea');
                (input as HTMLTextAreaElement).rows = 8;
                input.className = 'tinymce-editor';
                input.setAttribute('name', `values[${nombre}]`);
                break;

            case 'select': {
                const select = document.createElement('select');
                select.setAttribute('name', `values[${nombre}]`);

                (opcionesInput?.value ?? '')
                    .split('\n')
                    .map((opcion) => opcion.trim())
                    .filter(Boolean)
                    .forEach((opcion) => {
                        const item = document.createElement('option');
                        item.value = opcion;
                        item.textContent = opcion;
                        select.append(item);
                    });

                input = select;
                break;
            }

            case 'checkbox': {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.setAttribute('name', `values[${nombre}]`);
                checkbox.value = '1';
                input = checkbox;
                break;
            }

            case 'file': {
                const file = document.createElement('input');
                file.type = 'file';
                file.setAttribute('name', nombre); // Los archivos NO van dentro de values[]
                file.accept = 'image/*';
                input = file;
                break;
            }

            default: {
                // text, number, email, date, etc.
                const generico = document.createElement('input');
                generico.type = tipo;
                generico.setAttribute('name', `values[${nombre}]`);
                input = generico;
            }
        }

        label.append(input);
        return label;
    };

    /**
     * Crea una nueva fila del constructor (nombre + tipo + opciones + botón quitar)
     * y la añade al contenedor de filas.
     */
    const agregarFila = (): void => {
        const indice = contadorFilas++;

        const fila = document.createElement('div');
        fila.className = 'filaConstructor';
        fila.dataset.fieldRow = '';

        // --- Campo: Nombre ---
        const labelNombre = document.createElement('label');
        labelNombre.className = 'campo';
        labelNombre.append('Nombre del campo');

        const inputNombre = document.createElement('input');
        inputNombre.type = 'text';
        inputNombre.name = `builder_fields[${indice}][name]`;
        inputNombre.dataset.fieldName = '';
        inputNombre.placeholder = 'content';
        inputNombre.required = true;

        labelNombre.append(inputNombre);

        // --- Campo: Tipo ---
        const labelTipo = document.createElement('label');
        labelTipo.className = 'campo';
        labelTipo.append('Tipo');

        const selectTipo = document.createElement('select');
        selectTipo.name = `builder_fields[${indice}][type]`;
        selectTipo.dataset.fieldType = '';

        TIPOS_DE_CAMPO.forEach(({ value, label }) => {
            const opcion = document.createElement('option');
            opcion.value = value;
            opcion.textContent = label;
            selectTipo.append(opcion);
        });

        labelTipo.append(selectTipo);

        // --- Campo: Opciones (solo se usa cuando Tipo = "select") ---
        const labelOpciones = document.createElement('label');
        labelOpciones.className = 'campo opcionesConstructor';
        labelOpciones.append('Opciones del select');

        const textareaOpciones = document.createElement('textarea');
        textareaOpciones.name = `builder_fields[${indice}][options]`;
        textareaOpciones.dataset.fieldOptions = '';
        textareaOpciones.rows = 2;
        textareaOpciones.placeholder = 'Una opción por línea';

        labelOpciones.append(textareaOpciones);

        // --- Botón: Quitar fila ---
        const botonQuitar = document.createElement('button');
        botonQuitar.className = 'button';
        botonQuitar.type = 'button';
        botonQuitar.dataset.removeField = '';
        botonQuitar.textContent = 'Quitar';

        // --- Ensamblamos la fila ---
        fila.append(labelNombre, labelTipo, labelOpciones, botonQuitar);

        // Cualquier cambio en la fila debe refrescar la vista previa
        fila.querySelectorAll('input, select, textarea').forEach((campo) => {
            campo.addEventListener('input', renderPreview);
            campo.addEventListener('change', renderPreview);
        });

        // Botón para eliminar esta fila concreta
        botonQuitar.addEventListener('click', () => {
            fila.remove();
            renderPreview();
        });

        contenedorFilas.append(fila);
        renderPreview();
    };

    // --- 2. Arranque ---
    botonAgregarCampo.addEventListener('click', agregarFila);
    agregarFila(); // Empezamos con una fila visible por defecto
};