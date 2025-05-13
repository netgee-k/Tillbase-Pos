// fullscreen-logout.js
function triggerFullScreen() {
    const docEl = document.documentElement;
    if (docEl.requestFullscreen) {
        docEl.requestFullscreen();
    } else if (docEl.mozRequestFullScreen) { // Firefox
        docEl.mozRequestFullScreen();
    } else if (docEl.webkitRequestFullscreen) { // Chrome, Safari and Opera
        docEl.webkitRequestFullscreen();
    } else if (docEl.msRequestFullscreen) { // IE/Edge
        docEl.msRequestFullscreen();
    }
}

// Lock down the page to prevent user from navigating away
function lockDownPage() {
    // Prevent right-click
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
    });

    // Disable keyboard shortcuts for opening new tabs, printing, etc.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F12' || e.key === 'F11' || e.ctrlKey || e.altKey || e.key === 'Tab') {
            e.preventDefault();
        }
    });

    // Prevent navigation with the back button
    window.onpopstate = function () {
        history.pushState(null, document.title, location.href);
    };

    // Disable right-click, keyboard shortcuts, and certain actions
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' || e.key === 'Escape') {
            e.preventDefault();
        }
    });
}

// Call functions to lock the page and enter full-screen mode
window.onload = function () {
    triggerFullScreen();
    lockDownPage();
};
