import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';

document.querySelectorAll<HTMLDetailsElement>('.site-nav details').forEach((current) => {
    current.addEventListener('toggle', () => {
        if (!current.open) return;
        document.querySelectorAll<HTMLDetailsElement>('.site-nav details').forEach((other) => {
            if (other !== current) other.open = false;
        });
    });
});

tinymce.init({
    selector: '.tinymce-editor',
    language: 'es',
    base_url: '/tinymce',
    menubar: false,
    plugins: 'advlist autolink lists link image table code',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
    height: 320,
    resize: true,
    branding: false,
    statusbar: false,
    setup(editor) {
        editor.on('change', () => {
            editor.save();
        });
    },
});
