// handles password visibility toggle and shared ui behaviour

document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.togglePassword);
        if (!target) return;
        target.type = target.type === 'password' ? 'text' : 'password';
        btn.textContent = target.type === 'password' ? 'Show' : 'Hide';
    });
});

const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');

if (searchInput && clearBtn) {
    if (searchInput.value.length > 0) {
        clearBtn.style.display = 'block';
    }

    searchInput.addEventListener('input', function () {
        clearBtn.style.display = this.value.length ? 'block' : 'none';
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        searchInput.focus();
    });
}

const writerSearch = document.getElementById('writerSearch');
const clearWriterSearch = document.getElementById('clearWriterSearch');
const writerGrid = document.getElementById('writerGrid');
const writerNoResults = document.getElementById('writerNoResults');

if (writerSearch && writerGrid) {
    writerSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        clearWriterSearch.style.display = query.length ? 'flex' : 'none';
        let visible = 0;

        writerGrid.querySelectorAll('.writer-card-link').forEach((card) => {
            const name = card.dataset.name || '';
            const show = name.includes(query);
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (writerNoResults) {
            writerNoResults.style.display = visible === 0 ? '' : 'none';
        }
    });

    if (clearWriterSearch) {
        clearWriterSearch.addEventListener('click', function () {
            writerSearch.value = '';
            this.style.display = 'none';
            writerGrid.querySelectorAll('.writer-card-link').forEach((card) => {
                card.style.display = '';
            });
            if (writerNoResults) writerNoResults.style.display = 'none';
            writerSearch.focus();
        });
    }
}

const flagBtn = document.getElementById('flagBtn');
const flagModal = document.getElementById('flagModal');
const closeModal = document.getElementById('closeModal');
const overlay = document.querySelector('.modal-overlay');

if (flagBtn && flagModal) {
    flagBtn.addEventListener('click', () => {
        flagModal.classList.remove('hidden');
    });
}

if (closeModal && flagModal) {
    closeModal.addEventListener('click', () => {
        flagModal.classList.add('hidden');
    });
}

if (overlay && flagModal) {
    overlay.addEventListener('click', () => {
        flagModal.classList.add('hidden');
    });
}

const detailsInput = document.getElementById('flagDetails');
const charCount = document.getElementById('charCount');
const maxLength = 400;
const minFlagDetailsLength = 20;

if (detailsInput && charCount) {
    detailsInput.addEventListener('input', () => {
        const length = detailsInput.value.length;
        charCount.textContent = `${length}/${maxLength}`;
    });
}

function showToast(message) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastClose = document.getElementById('toastClose');

    toastMessage.textContent = message;
    toast.classList.remove('hidden');

    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);

    toastClose.onclick = () => {
        toast.classList.add('hidden');
    };
}

const flagForm = document.getElementById('flagForm');

if (flagForm && flagBtn && flagModal) {
    flagForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const detailsValue = (detailsInput?.value || '').trim();

        if (detailsValue.length < minFlagDetailsLength) {
            alert(`Please enter at least ${minFlagDetailsLength} characters so we can review the report properly.`);
            if (detailsInput) detailsInput.focus();
            return;
        }

        const formData = new FormData(flagForm);

        try {
            const res = await fetch('/actions/flag-article.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.ok) {
                flagBtn.disabled = true;
                flagBtn.classList.add('flagged');
                flagBtn.title = 'Already flagged';
                flagModal.classList.add('hidden');
                showToast('Report submitted successfully. Our team will review it.');
            } else {
                alert(data.error || 'Something went wrong.');
            }
        } catch (err) {
            console.error(err);
            alert('Network error.');
        }
    });
}

let paragraphs = [];
let currentIndex = 0;
let utterance = null;
let isReading = false;
let selectedVoice = null;

function loadArticle() {
    const nodes = document.querySelectorAll('.article-body p');
    paragraphs = Array.from(nodes);
}

function loadPreferredVoice() {
    const voices = speechSynthesis.getVoices();

    selectedVoice =
        voices.find((v) => v.name === 'Google UK English Female') ||
        voices.find((v) => v.name === 'Google US English') ||
        voices.find((v) => v.name === 'Microsoft Zira') ||
        voices.find((v) => v.name === 'Samantha') ||
        voices.find((v) => v.lang === 'en-US') ||
        voices[0];

    console.log('Using voice:', selectedVoice?.name);
}

speechSynthesis.onvoiceschanged = loadPreferredVoice;
window.onload = loadPreferredVoice;

function highlightParagraph(index) {
    paragraphs.forEach((p) => p.classList.remove('highlight-reading'));

    if (paragraphs[index]) {
        paragraphs[index].classList.add('highlight-reading');
        paragraphs[index].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}

function speak(index) {
    if (index >= paragraphs.length) {
        stopReading();
        return;
    }

    const text = paragraphs[index].innerText;

    utterance = new SpeechSynthesisUtterance(text);
    utterance.voice = selectedVoice;
    utterance.rate = 1;
    utterance.pitch = 1;

    highlightParagraph(index);

    utterance.onend = () => {
        currentIndex++;
        speak(currentIndex);
    };

    speechSynthesis.speak(utterance);
}

function readArticle() {
    if (paragraphs.length === 0) loadArticle();

    speechSynthesis.cancel();
    currentIndex = 0;
    isReading = true;
    speak(currentIndex);
}

function pauseReading() {
    if (speechSynthesis.speaking) {
        speechSynthesis.pause();
        isReading = false;
    }
}

function resumeReading() {
    if (speechSynthesis.paused) {
        speechSynthesis.resume();
        isReading = true;
    }
}

function stopReading() {
    speechSynthesis.cancel();
    currentIndex = 0;
    isReading = false;
    paragraphs.forEach((p) => p.classList.remove('highlight-reading'));
}

// share article func
document.addEventListener("DOMContentLoaded", () => {
    const shareBtn = document.getElementById("shareBtn");
    const shareMenu = document.getElementById("shareMenu");

    if (!shareBtn || !shareMenu) return;

    // Toggle menu
    shareBtn.addEventListener("click", async (e) => {
    e.stopPropagation();

    // Get clean URL
    const url = new URL(window.location.href);
    url.searchParams.delete("return");
    const shareUrl = url.toString();

    const title = document.title;

    // ✅ Native mobile share (if supported)
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (navigator.share && isMobile) {
        try {
            await navigator.share({
                title: title,
                text: title,
                url: shareUrl
            });
        } catch (err) {
            // User cancelled → do nothing
        }
        return;
    }

    // ❌ Fallback → show your menu (desktop)
    shareMenu.classList.toggle("hidden");
});

    // Close when clicking outside
    document.addEventListener("click", () => {
        shareMenu.classList.add("hidden");
    });
});


// detect if share link to platform or copy link and proceed to share its link to the selected platform
document.addEventListener("DOMContentLoaded", () => {
    const shareOptions = document.querySelectorAll(".share-option");

    shareOptions.forEach(option => {
        option.addEventListener("click", async (e) => {
            const platform = option.dataset.platform;

            // Get clean URL (remove &return=...)
            const url = new URL(window.location.href);
            url.searchParams.delete("return");

            const shareUrl = url.toString();

            if (platform === "copy") {
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    showToast("Link copied!");
                } catch (err) {
                    showToast("Failed to copy link");
                }
            }
    
            if (platform === "whatsapp") {
            const title = document.title;
            const text = `${shareUrl}\n\n${title}`;

            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;

            window.open(whatsappUrl, "_blank");
            }

            if (platform === "telegram") {
            const title = document.title;
            const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent("\n" + title)}`;
            window.open(telegramUrl, "_blank");
            shareMenu.classList.add("hidden");
            }
            if (platform === "email") {
            const title = document.title;
            const subject = encodeURIComponent("Check out this article on SharedSpace");
            const body = encodeURIComponent(`${shareUrl}\n\n${title}`);
            const mailtoUrl = `mailto:?subject=${subject}&body=${body}`;
            window.location.href = mailtoUrl;
            shareMenu.classList.add("hidden");
            }
            if (platform === "twitter") {
            const title = document.title;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(shareUrl)}`;
            window.open(twitterUrl, "_blank");
            shareMenu.classList.add("hidden");
            }
            if (platform === "print") {
            window.print();
            shareMenu.classList.add("hidden");
            }
        });
    });
});