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
