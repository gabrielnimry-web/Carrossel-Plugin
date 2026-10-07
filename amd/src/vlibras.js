// This file is part of Moodle - http://moodle.org/

/**
 * Loads the optional VLibras accessibility widget.
 *
 * @module local_coursecarousel/vlibras
 */
export const init = (scriptUrl, widgetUrl) => {
    if (window.coursecarouselVLibras) {
        return;
    }

    const initialise = () => {
        if (window.VLibras && !window.coursecarouselVLibras) {
            window.coursecarouselVLibras = new window.VLibras.Widget(widgetUrl);
        }
    };

    const existing = document.querySelector(`script[src="${scriptUrl}"]`);
    if (existing) {
        existing.addEventListener('load', initialise, {once: true});
        initialise();
        return;
    }

    const script = document.createElement('script');
    script.src = scriptUrl;
    script.async = true;
    script.addEventListener('load', initialise, {once: true});
    document.head.appendChild(script);
};
