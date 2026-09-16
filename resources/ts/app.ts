document.querySelectorAll<HTMLDetailsElement>('.site-nav details').forEach((current) => {
    current.addEventListener('toggle', () => {
        if (!current.open) return;
        document.querySelectorAll<HTMLDetailsElement>('.site-nav details').forEach((other) => {
            if (other !== current) other.open = false;
        });
    });
});
