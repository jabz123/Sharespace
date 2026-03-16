// handles the interest category chips in the onboarding page.
// users can only select up to 3 interests.
// if a 4th interest is selected, the earliest selected interest will be replaced.
// also updates the "0 / 3 selected" counter and allows clearing all selections.



// show number of characters written in the bio (max 150)
const bio = document.getElementById("bio");
const counter = document.getElementById("bioCounter");

if (bio && counter) {
    bio.addEventListener("input", () => {
    const length = bio.value.length;
    counter.textContent = length + " / 150";
    });
}


const checkboxes = document.querySelectorAll(".interest-checkbox");
const interestCounter = document.getElementById("interestCounter");
const clearInterestBtn = document.getElementById("clearInterests");

if (checkboxes.length > 0) {

let selected = [];

// Updates the interest counter text (example: 2 / 3 selected)
function updateCounter(){
if(interestCounter){
interestCounter.textContent = selected.length + " / 3 selected";
}
}

checkboxes.forEach(box => {
box.addEventListener("change", function () {
const chip = this.closest(".interest-chip");
if (this.checked) {
if (selected.length === 3) {
const first = selected.shift();
first.checked = false;
first.closest(".interest-chip").classList.remove("active");
}

selected.push(this);
chip.classList.add("active");
} else {
selected = selected.filter(item => item !== this);
chip.classList.remove("active");
}
updateCounter();
});
});

// Clears all selected interests and resets the counter.
if(clearInterestBtn){
clearInterestBtn.addEventListener("click", () => {
selected.forEach(box => {

box.checked = false;
box.closest(".interest-chip").classList.remove("active");
});
selected = [];
updateCounter();
});
}
updateCounter();
}

