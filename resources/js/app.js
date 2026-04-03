import './bootstrap';

// Activity Log Interactions
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('colour-change-circle')) {
        const colour = e.target.getAttribute('data-colour');
        const copyToClipboard = (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                textArea.style.top = "-9999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                return new Promise((res, rej) => {
                    document.execCommand('copy') ? res() : rej();
                    textArea.remove();
                });
            }
        };

        copyToClipboard(colour).then(() => {
            const originalText = e.target.getAttribute('data-colour');
            e.target.setAttribute('data-colour', 'Copied!');
            e.target.classList.add('scale-125'); // Visual pop
            setTimeout(() => {
                e.target.setAttribute('data-colour', originalText);
                e.target.classList.remove('scale-125');
            }, 1500);
        }).catch(err => {
            console.error('Copy failed', err);
        });
    }
});
