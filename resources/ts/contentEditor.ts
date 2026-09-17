import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';

export const initializeContentEditors = (): void => {
    // Buscamos solo textareas que aun no tengan TinyMCE montado encima.
    // Asi podemos llamar a esta funcion en cualquier pagina sin duplicar editores.
    const pendingEditors = Array.from(document.querySelectorAll<HTMLTextAreaElement>('.tinymce-editor'))
        .filter((element) => !element.classList.contains('tox-target'));

    if (pendingEditors.length === 0) return;

    pendingEditors.forEach((element) => {
        void tinymce.init({
            target: element,
            // TinyMCE 8 exige declarar la licencia.
            // Con 'gpl' usamos la licencia abierta y evitamos que desactive el editor.
            license_key: 'gpl',
            // El CSS ya entra por Vite con los imports de arriba.
            // Si no desactivamos esto, TinyMCE intenta pedir estos archivos por URL y da 404.
            skin: false,
            content_css: false,
            menubar: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
            height: 320,
            resize: true,
            branding: false,
            statusbar: false,
            content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            setup(editor) {
                editor.on('change', () => {
                    editor.save();
                });
            },
        }).catch((error: unknown) => {
            console.error('No se pudo inicializar el editor de contenido.', error);
        });
    });
};

export const removeContentEditors = (): void => {
    void tinymce.remove('.tinymce-editor');
};
