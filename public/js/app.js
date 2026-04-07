
// handles password visibility toggle for login and register forms
// controls the search bar behaviour such as showing and clearing the search input

//js shit

//show password
document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.togglePassword);
        if (!target) return;
        target.type = target.type === 'password' ? 'text' : 'password';
        btn.textContent = target.type === 'password' ? '👁' : '🙈';
    });
});

const searchInput = document.getElementById("searchInput");
const clearBtn = document.getElementById("clearSearch");

if(searchInput && clearBtn){

    if(searchInput.value.length > 0){
        clearBtn.style.display = "block";
    }

    searchInput.addEventListener("input", function(){
        clearBtn.style.display = this.value.length ? "block" : "none";
    });

    clearBtn.addEventListener("click", function(){
        searchInput.value = "";
        clearBtn.style.display = "none";
        searchInput.focus();
    });

}


// writer search filter (category writers page)
const writerSearch = document.getElementById('writerSearch');
const clearWriterSearch = document.getElementById('clearWriterSearch');
const writerGrid = document.getElementById('writerGrid');
const writerNoResults = document.getElementById('writerNoResults');

if (writerSearch && writerGrid) {
    writerSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        clearWriterSearch.style.display = query.length ? 'flex' : 'none';
        let visible = 0;
        writerGrid.querySelectorAll('.writer-card-link').forEach(card => {
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
            writerGrid.querySelectorAll('.writer-card-link').forEach(card => {
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

// open modal
if (flagBtn && flagModal) {
    flagBtn.addEventListener('click', () => {
        flagModal.classList.remove('hidden');
    });
}

// close modal (cancel button)
if (closeModal && flagModal) {
    closeModal.addEventListener('click', () => {
        flagModal.classList.add('hidden');
    });
}

// close modal (click outside)
if (overlay && flagModal) {
    overlay.addEventListener('click', () => {
        flagModal.classList.add('hidden');
    });
}


// character counter limit.
const detailsInput = document.getElementById('flagDetails');
const charCount = document.getElementById('charCount');
const maxLength = 100;

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

    // auto hide after 3s
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);

    // manual close
    toastClose.onclick = () => {
        toast.classList.add('hidden');
    };
}


// submit form for flagged article
const flagForm = document.getElementById('flagForm');

if (flagForm && flagBtn && flagModal) {
    flagForm.addEventListener('submit', async (e) => {
        e.preventDefault();

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
        }
        else {
            alert(data.error || 'Something went wrong.');
        }

        } catch (err) {
            console.error(err);
            alert('Network error.');
        }
    });
}


// TEXT TO SPEECH FOR ARTICLE CONTENT
let paragraphs = [];
let currentIndex = 0;
let utterance = null;
let isReading = false;
let selectedVoice = null;


// LOAD ARTICLE CONTENT
function loadArticle() {
    const nodes = document.querySelectorAll('.article-body p');
    paragraphs = Array.from(nodes);
}

// 
function loadPreferredVoice() {
    const voices = speechSynthesis.getVoices();

    selectedVoice =
        voices.find(v => v.name === "Google UK English Female") ||
        voices.find(v => v.name === "Google US English") ||
        voices.find(v => v.name === "Microsoft Zira") ||
        voices.find(v => v.name === "Samantha") ||
        voices.find(v => v.lang === "en-US") ||
        voices[0];

    console.log("Using voice:", selectedVoice?.name);
}

speechSynthesis.onvoiceschanged = loadPreferredVoice;
window.onload = loadPreferredVoice;


// HIGHLIGHT PARAGRAPH
function highlightParagraph(index) {
    paragraphs.forEach(p => p.classList.remove('highlight-reading'));

    if (paragraphs[index]) {
        paragraphs[index].classList.add('highlight-reading');
        paragraphs[index].scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }
}


// SPEAK FUNCTION
function speak(index) {

    if (index >= paragraphs.length) {
        stopReading(); // auto reset when finished
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



// ▶ READ ARTICLE

function readArticle() {

    if (paragraphs.length === 0) loadArticle();

    //  Always reset to prevent bugs
    speechSynthesis.cancel();

    currentIndex = 0;
    isReading = true;

    speak(currentIndex);
}



// ⏹ STOP READING
function stopReading() {
    speechSynthesis.cancel();

    isReading = false;
    currentIndex = 0;

    paragraphs.forEach(p => p.classList.remove('highlight-reading'));
}


// STOP AUDIO WHEN USER LEAVE PAGE OR PAGE NOT ACTIVE
// Leave page
window.addEventListener("beforeunload", () => {
    speechSynthesis.cancel();
});

// Switch tab / minimize
document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        speechSynthesis.cancel();
    }
});

// Click any link
document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", () => {
        speechSynthesis.cancel();
    });
});
