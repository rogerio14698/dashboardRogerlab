export const initializeNavigation = (): void => {
    document.querySelectorAll<HTMLDetailsElement>('.navegacionSitio details').forEach((current) => {
        current.addEventListener('toggle', () => {
            if (!current.open) return;

            document.querySelectorAll<HTMLDetailsElement>('.navegacionSitio details').forEach((other) => {
                if (other !== current) other.open = false;
            });
        });
    });
};
